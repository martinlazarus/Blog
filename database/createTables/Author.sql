CREATE TABLE Author
(
    AuthorId INT(11) NOT NULL AUTO_INCREMENT,
    DisplayName VARCHAR(100) NOT NULL,
    FirstName VARCHAR(50),
    LastName VARCHAR(50),
    CONSTRAINT PK_Author_AuthorId PRIMARY KEY (AuthorId)
)