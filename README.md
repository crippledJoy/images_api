# images_api
Post metadata, get all metadata or an image by id

# install
Place the code in your public folder
Create a database
Create table images:
```SQL
CREATE TABLE IF NOT EXISTS `images` (
  `pictureid` int(19) NOT NULL AUTO_INCREMENT,
  `picture_title` varchar(128) CHARACTER SET utf8 NOT NULL,
  `picture_url` varchar(128) CHARACTER SET utf8 NOT NULL,
  `picture_description` mediumtext CHARACTER SET utf8,
  `picture_path` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`pictureid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```
Put your database credentials in config.php

# use
Call the api and provide one of the following services: upload (+provide filetype csv), getallMetaData or getImageById.
