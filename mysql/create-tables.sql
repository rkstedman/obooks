-- Rachael Stedman
-- 02/08/2010

-- scp filename rstedman@puma.wellesley.edu:filename

-- This file creates three tables in the 'rkstedman' database. The Directory
-- table contains an auto-increment id field that is a foreign key in both 
-- the Phone table and Address table.

-- use rstedman database
use rstedman;

-- first, drop any existing tables.
drop table if exists post;
drop table if exists book;
drop table if exists seller;

-- define the person table
CREATE TABLE seller (
firstname varchar(50), 
lastname varchar(50), 
email varchar(100) not null primary key)
-- table constraints follow
ENGINE = InnoDB;

-- define the book table
CREATE TABLE book (
isbn varchar(50) not null primary key,
title varchar(100),
author varchar(50),
publisher varchar(50) )
-- table constraints follow
ENGINE = InnoDB;


-- define the phone table
CREATE TABLE post (
pid int not null auto_increment primary key, 
email varchar(50) not null references seller(email),
isbn varchar(50) not null references book(isbn),
price decimal (5,2),
`condition` varchar(100),
course varchar(100) DEFAULT null,
`comments` varchar(300) DEFAULT null,
posttime TIMESTAMP DEFAULT NOW(),
INDEX (email),		-- necessary for referential integrity checking
foreign key (email) references seller(email) on delete restrict,
INDEX (isbn),		-- necessary for referential integrity checking
foreign key (isbn) references book(isbn) on delete restrict )
-- table constraints follow
ENGINE = InnoDB;

insert into seller values
('Alivia', 'Ruff','aruff@students.olin.edu'),
('Katelyn', 'Dallimore','kdallimore@students.olin.edu'),
('Gene', 'Masters','gmasters@students.olin.edu'),
('Nyasia', 'Fauntleroy','nfaunt@students.olin.edu'),
('Zaria', 'Burman','zburman@students.olin.edu');

insert into book values
('013805326X','Introduction to Electrodynamics','David J. Griffiths','Benjamin Cummings'),
('0876390599','The Future of Music: Manifesto for the Digital Music Revolution','David Kusek','Berklee Press');

-- note to self, need to change primary key to not email and isbn, because could technically be selling two copies, grrr...
insert into post values
(0,'aruff@students.olin.edu','013805326X',10.28,'Hardcover, Good condition','Electrodynamics and Magnetics','some highlighting, good read',NOW()),
(0, 'kdallimore@students.olin.edu','0876390599',80,'Softcover, some highlighting',null,null,NOW()),
(0, 'zburman@students.olin.edu','0876390599',75,'Softcover, rough corners and some scratches','Music Class','very interesting book, price negotiable',NOW());

-- have this file be self-descriptive when loaded

select * from seller;
select * from book;
select * from post;