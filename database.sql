CREATE TABLE `users` (
    `username` varchar(20) NOT NULL,
    `password` char(60) NOT NULL,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `streak` int NOT NULL DEFAULT 0,
    `points` bigint NOT NULL DEFAULT 100,
    `mod` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`username`)
);

CREATE TABLE `predictions` (
    `id` int NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` varchar(1000) NULL,
    `user` varchar(20) NOT NULL,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ended` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `answer` int NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `pred_user` FOREIGN KEY (`user`) REFERENCES `users` (`username`)
);

CREATE TABLE `choices` (
    `id` int NOT NULL AUTO_INCREMENT,
    `prediction` int NOT NULL,
    `name` varchar(100) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `prediction` FOREIGN KEY (`prediction`) REFERENCES `predictions` (`id`)
);

CREATE TABLE `votes` (
    `user` varchar(20) NOT NULL,
    `prediction` int NOT NULL,
    `choice` int NOT NULL,
    `points` bigint NOT NULL,
    PRIMARY KEY (`user`, `prediction`),
    CONSTRAINT `vote_user` FOREIGN KEY (`user`) REFERENCES `users` (`username`),
    CONSTRAINT `vote_prediction` FOREIGN KEY (`prediction`) REFERENCES `predictions` (`id`),
    CONSTRAINT `choice` FOREIGN KEY (`choice`) REFERENCES `choices` (`id`)
);

ALTER TABLE `predictions` 
    ADD CONSTRAINT `answer` FOREIGN KEY (`answer`) REFERENCES `choices` (`id`);

CREATE EVENT `daily_points` ON SCHEDULE EVERY 1 DAY STARTS '2023-06-01 00:00:00' ON COMPLETION PRESERVE ENABLE DO UPDATE `users` SET `points` = (points + 10 + streak) WHERE `updated` > NOW() - INTERVAL 1 DAY;
CREATE EVENT `daily_streak_increment` ON SCHEDULE EVERY 1 DAY STARTS '2023-06-01 00:00:01' ON COMPLETION PRESERVE ENABLE DO UPDATE `users` SET `streak` = streak + 1 WHERE `updated` > NOW() - INTERVAL 1 DAY;
CREATE EVENT `daily_streak_reset` ON SCHEDULE EVERY 1 DAY STARTS '2023-06-01 00:00:02' ON COMPLETION PRESERVE ENABLE DO UPDATE `users` SET `streak` = 0 WHERE `updated` < NOW() - INTERVAL 1 DAY;