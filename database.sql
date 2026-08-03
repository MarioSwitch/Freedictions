CREATE TABLE `users` (
	`username` varchar(20) NOT NULL,
	`password` char(60) NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`streak` int NOT NULL DEFAULT 0,
	`chips` bigint NOT NULL DEFAULT 100,
	`mod` tinyint(1) NOT NULL DEFAULT 0,
	`extra` varchar(1000) DEFAULT NULL,
	PRIMARY KEY (`username`)
);

CREATE TABLE `predictions` (
	`id` int NOT NULL AUTO_INCREMENT,
	`title` varchar(255) NOT NULL,
	`description` varchar(1000) NULL,
	`user` varchar(20) NOT NULL,
	`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`ended` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`approved` tinyint(1) NOT NULL DEFAULT 0,
	`answered` timestamp NULL,
	`answer` int NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `prediction_user` FOREIGN KEY (`user`) REFERENCES `users` (`username`) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE `choices` (
	`id` int NOT NULL AUTO_INCREMENT,
	`prediction` int NOT NULL,
	`name` varchar(100) NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `choice_prediction` FOREIGN KEY (`prediction`) REFERENCES `predictions` (`id`)
);

CREATE TABLE `bets` (
	`user` varchar(20) NOT NULL,
	`prediction` int NOT NULL,
	`choice` int NOT NULL,
	`chips` bigint NOT NULL,
	PRIMARY KEY (`user`, `prediction`),
	CONSTRAINT `bet_user` FOREIGN KEY (`user`) REFERENCES `users` (`username`) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT `bet_prediction` FOREIGN KEY (`prediction`) REFERENCES `predictions` (`id`),
	CONSTRAINT `bet_choice` FOREIGN KEY (`choice`) REFERENCES `choices` (`id`)
);

CREATE TABLE `notifications` (
	`user` varchar(20) NOT NULL,
	`text` varchar(1000) NOT NULL,
	`sent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`read` tinyint(1) NOT NULL DEFAULT 0,
	CONSTRAINT `notification_user` FOREIGN KEY (`user`) REFERENCES `users` (`username`) ON UPDATE CASCADE ON DELETE CASCADE
);

ALTER TABLE `predictions` 
	ADD CONSTRAINT `answer` FOREIGN KEY (`answer`) REFERENCES `choices` (`id`);


DELIMITER $$
CREATE PROCEDURE `UserCreate`
	(
		p_username varchar(20),
		p_password char(60)
	)
	BEGIN
		INSERT INTO `users` (`username`, `password`) VALUES (p_username, p_password);
	END $$

CREATE PROCEDURE `UserEdit`
	(
		p_username varchar(20),
		p_password char(60),
		p_created timestamp,
		p_updated timestamp,
		p_streak int,
		p_chips bigint,
		p_mod tinyint(1),
		p_extra varchar(1000),
		p_username_old varchar(20)
	)
	BEGIN
		UPDATE `users` SET `username` = p_username, `password` = p_password, `created` = p_created, `updated` = p_updated, `streak` = p_streak, `chips` = p_chips, `mod` = p_mod, `extra` = p_extra WHERE `username` = p_username_old;
	END $$

CREATE PROCEDURE `UserDelete`
	(
		p_username varchar(20)
	)
	BEGIN
		DELETE FROM `users` WHERE `username` = p_username;
	END $$

CREATE PROCEDURE `UserPassword`
	(
		p_username varchar(20),
		p_password char(60)
	)
	BEGIN
		UPDATE `users` SET `password` = p_password WHERE `username` = p_username;
	END $$

CREATE PROCEDURE `ModqueueApprove`
	(
		p_id int
	)
	BEGIN
		DECLARE v_user varchar(20);
		DECLARE v_text varchar(1000);

		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			ROLLBACK;
			RESIGNAL;
		END;

		START TRANSACTION;
			UPDATE `predictions` SET `created` = NOW(), `approved` = 1 WHERE `id` = p_id;
			SET v_user = (SELECT `user` FROM `predictions` WHERE `id` = p_id);
			SET v_text = CONCAT('APPROVED:', p_id);
			INSERT INTO `notifications` (`user`, `text`) VALUES (v_user, v_text);
		COMMIT;
	END $$

CREATE PROCEDURE `ModqueueReject`
	(
		p_id int
	)
	BEGIN
		DECLARE v_user varchar(20);
		DECLARE v_text varchar(1000);

		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			ROLLBACK;
			RESIGNAL;
		END;

		START TRANSACTION;
			SET v_user = (SELECT `user` FROM `predictions` WHERE `id` = p_id);
			SET v_text = CONCAT('REJECTED:', p_id);
			INSERT INTO `notifications` (`user`, `text`) VALUES (v_user, v_text);
			CALL `PredictionDelete`(p_id);
		COMMIT;
	END $$

CREATE PROCEDURE `PredictionCreate`
	(
		p_title varchar(255),
		p_description varchar(1000),
		p_user varchar(20),
		p_ended timestamp,
		p_approved tinyint(1),
		p_choices json
	)
	BEGIN
		DECLARE i int DEFAULT 0;
		DECLARE v_id int;
		DECLARE v_name varchar(100);

		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			ROLLBACK;
			RESIGNAL;
		END;

		START TRANSACTION;
			INSERT INTO `predictions` (`title`, `description`, `user`, `ended`, `approved`) VALUES (p_title, p_description, p_user, p_ended, p_approved);
			SET v_id = LAST_INSERT_ID();
			WHILE i < JSON_LENGTH(p_choices) DO
				SET v_name = JSON_UNQUOTE(JSON_EXTRACT(p_choices, CONCAT('$[', i, ']')));
				INSERT INTO `choices` (`prediction`, `name`) VALUES (v_id, v_name);
				SET i = i + 1;
			END WHILE;
		COMMIT;

		SELECT v_id;
	END $$

CREATE PROCEDURE `PredictionEdit`
	(
		p_id int,
		p_title varchar(255),
		p_description varchar(1000),
		p_user varchar(20),
		p_created timestamp,
		p_ended timestamp,
		p_choices json,
		p_choices_id json
	)
	BEGIN
		DECLARE i int DEFAULT 0;
		DECLARE v_choice_id int;
		DECLARE v_choice_name varchar(100);

		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			ROLLBACK;
			RESIGNAL;
		END;

		START TRANSACTION;
			UPDATE `predictions` SET `title` = p_title, `description` = p_description, `user` = p_user, `created` = p_created, `ended` = p_ended WHERE `id` = p_id;
			WHILE i < JSON_LENGTH(p_choices) DO
				SET v_choice_id = JSON_UNQUOTE(JSON_EXTRACT(p_choices_id, CONCAT('$[', i, ']')));
				SET v_choice_name = JSON_UNQUOTE(JSON_EXTRACT(p_choices, CONCAT('$[', i, ']')));
				UPDATE `choices` SET `name` = v_choice_name WHERE `id` = v_choice_id;
				SET i = i + 1;
			END WHILE;
		COMMIT;
	END $$

CREATE PROCEDURE `PredictionBet`
	(
		p_user varchar(20),
		p_prediction int,
		p_choice int,
		p_chips bigint
	)
	BEGIN
		DECLARE v_already_bet int;

		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			ROLLBACK;
			RESIGNAL;
		END;

		START TRANSACTION;
			SET v_already_bet = (SELECT `choice` FROM `bets` WHERE `user` = p_user AND `prediction` = p_prediction);
			IF v_already_bet IS NOT NULL THEN
				UPDATE `bets` SET `chips` = (chips + p_chips) WHERE `user` = p_user AND `prediction` = p_prediction;
			ELSE
				INSERT INTO `bets` (`user`, `prediction`, `choice`, `chips`) VALUES (p_user, p_prediction, p_choice, p_chips);
			END IF;
			UPDATE `users` SET `chips` = (chips - p_chips) WHERE `username` = p_user;
		COMMIT;
	END $$

CREATE PROCEDURE `PredictionClose`
	(
		p_id int
	)
	BEGIN
		UPDATE `predictions` SET `ended` = NOW() WHERE `id` = p_id;
	END $$

CREATE PROCEDURE `DailyUpdate` ()
	BEGIN
		DECLARE EXIT HANDLER FOR SQLEXCEPTION
		BEGIN
			ROLLBACK;
			RESIGNAL;
		END;

		START TRANSACTION;
			UPDATE `users` SET `chips` = (chips + 10 + streak), `streak` = streak + 1 WHERE `updated` >= NOW() - INTERVAL 1 DAY;
			UPDATE `users` SET `streak` = 0 WHERE `updated` < NOW() - INTERVAL 1 DAY;
			INSERT INTO `notifications` (`user`, `text`) SELECT `username`, CONCAT('DAILY:', `streak`) FROM `users` WHERE `updated` >= NOW() - INTERVAL 1 DAY;
			INSERT INTO `notifications` (`user`, `text`) SELECT `username`, 'DAILY:RESET' FROM `users` WHERE `updated` >= NOW() - INTERVAL 2 DAY AND `updated` < NOW() - INTERVAL 1 DAY;
		COMMIT;
	END $$

CREATE EVENT `DailyUpdateEvent` ON SCHEDULE EVERY 1 DAY STARTS '2024-11-14 00:00:00' ON COMPLETION PRESERVE ENABLE DO
	CALL `DailyUpdate`;

DELIMITER ;