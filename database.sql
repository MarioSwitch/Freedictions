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
	`answered` timestamp NULL,
	`answer` int NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `prediction_user` FOREIGN KEY (`user`) REFERENCES `users` (`username`)
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
	CONSTRAINT `bet_user` FOREIGN KEY (`user`) REFERENCES `users` (`username`),
	CONSTRAINT `bet_prediction` FOREIGN KEY (`prediction`) REFERENCES `predictions` (`id`),
	CONSTRAINT `bet_choice` FOREIGN KEY (`choice`) REFERENCES `choices` (`id`)
);

CREATE TABLE `notifications` (
	`id` int NOT NULL AUTO_INCREMENT,
	`user` varchar(20) NOT NULL,
	`text` varchar(1000) NOT NULL,
	`sent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`read` tinyint(1) NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	CONSTRAINT `notification_user` FOREIGN KEY (`user`) REFERENCES `users` (`username`)
);

ALTER TABLE `predictions` 
	ADD CONSTRAINT `answer` FOREIGN KEY (`answer`) REFERENCES `choices` (`id`);


DELIMITER $$
CREATE EVENT `daily_update` ON SCHEDULE EVERY 1 DAY STARTS '2024-11-14 00:00:00' ON COMPLETION PRESERVE ENABLE DO
	BEGIN
		UPDATE `users` SET `chips` = (chips + 10 + streak) WHERE `updated` >= NOW() - INTERVAL 1 DAY;
		UPDATE `users` SET `streak` = streak + 1 WHERE `updated` >= NOW() - INTERVAL 1 DAY;
		UPDATE `users` SET `streak` = 0 WHERE `updated` < NOW() - INTERVAL 1 DAY;
	END $$
	
	INSERT INTO `notifications` (`user`, `text`)
		SELECT `username`, CONCAT('DAILY:', `streak`)
		FROM `users`
		WHERE `updated` >= NOW() - INTERVAL 1 DAY $$

	INSERT INTO `notifications` (`user`, `text`)
		SELECT `username`, 'DAILY:RESET'
		FROM `users`
		WHERE `updated` >= NOW() - INTERVAL 2 DAY
		AND `updated` < NOW() - INTERVAL 1 DAY $$
DELIMITER ;