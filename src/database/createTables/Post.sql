CREATE TABLE Post
(
    PostId INT AUTO_INCREMENT,
    Title VARCHAR(300),
    Content TEXT,
    Created_at VARCHAR(30), 
    Updated_at DATETIME,
    CONSTRAINT PK_Post_PostId PRIMARY KEY (PostId)
)