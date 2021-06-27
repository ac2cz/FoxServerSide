DROP PROCEDURE IF EXISTS ArchiveSTPHeaders;

DELIMITER //
CREATE PROCEDURE ArchiveSTPHeaders()
BEGIN

DECLARE stp_header_archive_max_date timestamp ;
DECLARE stp_header_max_date timestamp;

start transaction;

/* First work out the newest record in the STP_HEADER table and the ARCHIVE table */
select max(date_time) from STP_HEADER into stp_header_max_date;
select max(date_time) from STP_HEADER_ARCHIVE into stp_header_archive_max_date;
if (ISNULL(stp_header_archive_max_date)) then set stp_header_archive_max_date = '2010-01-01 00:00:00';
end if;
select stp_header_archive_max_date;

/* Then insert into the ARCHIVE table all records from STP_HEADER that are older then
   the archive period */
insert into STP_HEADER_ARCHIVE
(stpDate, id, resets, uptime, type, sequenceNumber, length, source, receiver, frequency, rx_location, receiver_rf, demodulator, measuredTCA, measuredTCAfrequency, date_time)
(select stpDate, id, resets, uptime, type, sequenceNumber, length, source, receiver, frequency, rx_location, receiver_rf, demodulator, measuredTCA, measuredTCAfrequency, date_time
from STP_HEADER
where STP_HEADER.date_time > stp_header_archive_max_date
and timestampdiff(DAY, date_time, stp_header_max_date) > 30);

/* Clear the ARCHIVE count table  */
delete from STP_HEADER_ARCHIVE_COUNT;

/* Put in a count for the total records in the ARCHIVE by station */
insert into STP_HEADER_ARCHIVE_COUNT
(id, receiver, duv_total, highspeed_total, psk_total)
(select id, receiver, 
sum(case when source like '%duv' then 1 else 0 end) duv_total, 
sum(case when source like '%highspeed' then 1 else 0 end) highspeed_total,
sum(case when source like '%bpsk' then 1 else 0 end) psk_total 
from STP_HEADER_ARCHIVE group by id, receiver);

/* Now add a count for the last month */
select max(date_time) from STP_HEADER_ARCHIVE into stp_header_archive_max_date;

insert into STP_HEADER_ARCHIVE_COUNT
(id, receiver, duv_total, highspeed_total, psk_total, period)
(select id, receiver, 
sum(case when source like '%duv' then 1 else 0 end) duv_total, 
sum(case when source like '%highspeed' then 1 else 0 end) highspeed_total,
sum(case when source like '%bpsk' then 1 else 0 end) psk_total,
30 
from STP_HEADER_ARCHIVE
where timestampdiff(DAY, date_time, stp_header_archive_max_date) < 30
and receiver = "AC2CZ"
group by id, receiver);

/* Then store the totals for each spacecraft so we have it ready */
delete from STP_ARCHIVE_TOTALS;
insert into STP_ARCHIVE_TOTALS
(id, total)
(select id, count(id) from STP_HEADER_ARCHIVE group by id);

delete from STP_HEADER
where STP_HEADER.date_time > stp_header_archive_max_date
and timestampdiff(DAY, date_time, stp_header_max_date) > 30;

commit;

END //
DELIMITER ;
