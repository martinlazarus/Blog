CREATE TABLE Post
(
    PostId INT AUTO_INCREMENT,
    CategoryId INT,
    AuthorId INT,
    Title VARCHAR(300) NOT NULL,
    Content TEXT NOT NULL,
    Created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, 
    Updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT PK_Post_PostId PRIMARY KEY (PostId),
    CONSTRAINT FK_Post_CategoryId FOREIGN KEY (CategoryId) REFERENCES Category(CategoryId),
    CONSTRAINT FK_Post_AuthorId FOREIGN KEY (AuthorId) REFERENCES Author(AuthorId)
)