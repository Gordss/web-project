CREATE TABLE IF NOT EXISTS `User`(
    `Id` INT AUTO_INCREMENT PRIMARY KEY,
    `Username` VARCHAR(150) NOT NULL UNIQUE,
    `Password` VARCHAR(200) NOT NULL
); 

CREATE TABLE IF NOT EXISTS `Convertion`(
    `Id` INT AUTO_INCREMENT PRIMARY KEY,
    `Fk_User_Id` INT NOT NULL,
    `CreateDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `Options` VARCHAR(2048) NOT NULL,
    `SourcePath` VARCHAR(500) NOT NULL,
    `SourceName` VARCHAR(200) NOT NULL,
    `SourceExtension` VARCHAR(50) NOT NULL,
    `Md5_Sum` VARCHAR(500) NOT NULL,
    FOREIGN KEY (`Fk_User_Id`)
      REFERENCES `User`(`Id`)
);