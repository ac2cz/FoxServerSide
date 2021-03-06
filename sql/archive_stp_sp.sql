DROP PROCEDURE IF EXISTS ArchiveSTPHeaders_v2;

DELIMITER //
CREATE PROCEDURE ArchiveSTPHeaders_v2()
BEGIN

DECLARE stp_header_max_date timestamp;

start transaction;

select now() into stp_header_max_date;

/* Insert old records into archive */
insert ignore into STP_HEADER_ARCHIVE
(stpDate, id, resets, uptime, type, sequenceNumber, length, source, receiver, frequency, rx_location, receiver_rf, demodulator, measuredTCA, measuredTCAfrequency, date_time)
(select stpDate, id, resets, uptime, type, sequenceNumber, length, source, receiver, frequency, rx_location, receiver_rf, demodulator, measuredTCA, measuredTCAfrequency, date_time
from STP_HEADER
where timestampdiff(DAY, date_time, stp_header_max_date) > 30);

/* Reset the archive count which we use to make leaderboard go fast*/
delete from STP_HEADER_ARCHIVE_COUNT;

insert into STP_HEADER_ARCHIVE_COUNT
(id, receiver, duv_total, highspeed_total, psk_total)
(select id, receiver, 
sum(case when source like '%duv' then 1 else 0 end) duv_total, 
sum(case when source like '%highspeed' then 1 else 0 end) highspeed_total,
sum(case when source like '%bpsk' then 1 else 0 end) psk_total 
from STP_HEADER_ARCHIVE group by id, receiver);

/* Reset the Archive totals which are by spacecraft */
delete from STP_ARCHIVE_TOTALS;
insert into STP_ARCHIVE_TOTALS
(id, total)
(select id, count(id) from STP_HEADER_ARCHIVE group by id);

/* Purge the records we moved */
delete from STP_HEADER
where timestampdiff(DAY, date_time, stp_header_max_date) > 30;

commit;

END //
DELIMITER ;
