
CREATE DATABASE IF NOT EXISTS bitrixdb1 CHARACTER SET cp1251 COLLATE cp1251_general_ci;

CREATE USER bitrixuser1@localhost IDENTIFIED BY "bitrixpassword1";
GRANT ALL PRIVILEGES ON bitrixdb1.* TO bitrixuser1@localhost WITH GRANT OPTION;
