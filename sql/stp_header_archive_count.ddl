DROP TABLE IF EXISTS STP_HEADER_ARCHIVE_COUNT;

create table STP_HEADER_ARCHIVE_COUNT
(id int not null,
receiver varchar(35) not null,
duv_total int,
highspeed_total int);
