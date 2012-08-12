-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `users` (
  `user_id` INT NOT NULL ,
  `access_token` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `acces_token_UNIQUE` (`acces_token` ASC))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `playlists`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `playlists` (
  `playlist_id` INT NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(100) NOT NULL ,
  `description` VARCHAR(255) NOT NULL ,
  `user_id` INT NOT NULL ,
  `deleted` TINYINT NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`playlist_id`),
  INDEX `playlists_users` (`user_id` ASC))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `tracks`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tracks` (
  `track_id` INT NOT NULL ,
  PRIMARY KEY (`track_id`))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `tracks_playlists`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tracks_playlists` (
  `track_id` INT NOT NULL ,
  `playlist_id` INT NOT NULL ,
  INDEX `tracks_users_playlists` (`playlist_id` ASC))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `actions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `actions` (
  `action_id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NULL ,
  PRIMARY KEY (`action_id`))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `actions_users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `actions_users` (
  `action_id` INT NOT NULL ,
  `user_id` INT NOT NULL ,
  `id` INT NULL ,
  INDEX `actions_users_actions` (`action_id` ASC) ,
  INDEX `actions_users_users` (`user_id` ASC))
ENGINE = MyISAM;