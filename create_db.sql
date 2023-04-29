CREATE TABLE `user_db`.`user` 
(`user_id` INT NOT NULL AUTO_INCREMENT , 
`username` TEXT NOT NULL , 
`password` TEXT NOT NULL , 
`user_infected` BOOLEAN NOT NULL DEFAULT FALSE , 
`user_susceptible` BOOLEAN NOT NULL DEFAULT FALSE , 
`questionnaire_completed` BOOLEAN NOT NULL DEFAULT FALSE , 
`seed` BOOLEAN NOT NULL DEFAULT FALSE , 
PRIMARY KEY (`user_id`));

CREATE TABLE `user_db`.`friends` 
(`friend_id` INT NOT NULL AUTO_INCREMENT , 
`user_id` INT NOT NULL , 
`list_of_friends` TEXT NOT NULL , 
`blocked_list` TEXT NOT NULL , 
PRIMARY KEY (`friend_id`));

CREATE TABLE `user_db`.`winner` 
(`winner_id` INT NOT NULL AUTO_INCREMENT , 
`user_id` INT NOT NULL , 
`round` INT NOT NULL , 
`network` TEXT NOT NULL , 
`rank` INT NOT NULL , 
PRIMARY KEY (`winner_id`));

CREATE TABLE `user_db`.`timeline` 
(`timeline_id` INT NOT NULL AUTO_INCREMENT , 
`user_id` INT NOT NULL , 
`received_from` INT NOT NULL , 
`sent_to` INT NOT NULL , 
`received_times` INT NOT NULL , 
`infected` INT NOT NULL , 
PRIMARY KEY (`timeline_id`));

CREATE TABLE `user_db`.`user_score` 
(`score_id` INT NOT NULL AUTO_INCREMENT , 
`user_id` INT NOT NULL , 
`items` TEXT NOT NULL , 
`items_total_value` INT NOT NULL , 
`times_infected` INT NOT NULL , 
`times_recovered` INT NOT NULL , 
`clean_sent` INT NOT NULL , 
`clean_received` INT NOT NULL , 
`infected_sent` INT NOT NULL , 
`infected_received` INT NOT NULL , 
`infected_by` INT NOT NULL , 
`final_score` INT NOT NULL , 
PRIMARY KEY (`score_id`));

CREATE TABLE `user_db`.`round_score` 
(`round_score_id` INT NOT NULL AUTO_INCREMENT , 
`user_id` INT NOT NULL , 
`total` INT NOT NULL , 
`round_num` INT NOT NULL , 
`infected_by_list` INT NOT NULL , 
`clean_sent` INT NOT NULL , 
`clean_received` INT NOT NULL , 
`infected_sent` INT NOT NULL , 
`infected_received` INT NOT NULL , 
PRIMARY KEY (`round_score_id`));

