DROP PROCEDURE IF EXISTS StpLeaderboardTotalsById;

DELIMITER //
CREATE PROCEDURE StpLeaderboardTotalsById(IN foxId int(11))
BEGIN

/* Init the Count table with totals from the live STP_HEADER table */
DROP TABLE IF EXISTS STP_HEADER_COUNT_TEMP;
DROP TABLE IF EXISTS STP_HEADER_ARCHIVE_TEMP;

create temporary table STP_HEADER_COUNT_TEMP as
select id, receiver, sum(case when source like '%duv' then 1 else 0 end) DUV, 
sum(case when source like '%highspeed' then 1 else 0 end) HighSpeed, 
sum(case when timestampdiff(DAY,date_time,now()) < 7 then 1 else 0 end) last 
from STP_HEADER 
where id=foxId
group by receiver order by DUV DESC;

/* select '1';
select receiver, sum(DUV), sum(HighSpeed), sum(last) 
from STP_HEADER_COUNT_TEMP
group by receiver;
*/

/* Update the Count table any totals from the STP_HEADER_ARCHIVE_COUNT table */
update STP_HEADER_COUNT_TEMP 
INNER JOIN STP_HEADER_ARCHIVE_COUNT 
ON STP_HEADER_COUNT_TEMP.receiver = STP_HEADER_ARCHIVE_COUNT.receiver
and STP_HEADER_COUNT_TEMP.id = STP_HEADER_ARCHIVE_COUNT.id
set STP_HEADER_COUNT_TEMP.DUV = STP_HEADER_COUNT_TEMP.DUV + STP_HEADER_ARCHIVE_COUNT.duv_total,
    STP_HEADER_COUNT_TEMP.HighSpeed = STP_HEADER_COUNT_TEMP.HighSpeed + STP_HEADER_ARCHIVE_COUNT.highspeed_total;

/* Inset into the Count table any totals from the STP_HEADER_ARCHIVE_COUNT table 
   which are not already in the TEMP table */
create temporary table STP_HEADER_ARCHIVE_TEMP
as 
select t1.id, t1.receiver, duv_total, highspeed_total 
from STP_HEADER_ARCHIVE_COUNT as t1 
left outer join STP_HEADER_COUNT_TEMP as t2
on t1.receiver = t2.receiver
and t1.id = t2.id
where t2.id is null
and t1.id = foxId;

/* select 'A';
select receiver, sum(duv_total), sum(highspeed_total)
from STP_HEADER_ARCHIVE_TEMP
group by receiver;
*/

insert into STP_HEADER_COUNT_TEMP
(id, receiver, DUV, HighSpeed)
select id, receiver, duv_total, highspeed_total
from STP_HEADER_ARCHIVE_TEMP;

/* select '2';
select receiver, sum(DUV), sum(HighSpeed), sum(last) 
from STP_HEADER_COUNT_TEMP
group by receiver;
*/

select receiver, sum(DUV) DUV, sum(HighSpeed) HighSpeed, 
sum(DUV) + sum(HighSpeed) total, 
sum(last) last 
from STP_HEADER_COUNT_TEMP
group by receiver
order by total DESC;

drop table STP_HEADER_COUNT_TEMP;
drop table STP_HEADER_ARCHIVE_TEMP;
END //
DELIMITER ;
