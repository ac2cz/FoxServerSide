DROP PROCEDURE IF EXISTS ArchiveSTPHeaders;

DELIMITER //
CREATE PROCEDURE ArchiveSTPHeaders()
BEGIN

DECLARE stp_header_archive_max_date timestamp ;
DECLARE stp_header_max_date timestamp;

start transaction;

select max(date_time) from STP_HEADER into stp_header_max_date;
select max(date_time) from STP_HEADER_ARCHIVE into stp_header_archive_max_date;
if (ISNULL(stp_header_archive_max_date)) then set stp_header_archive_max_date = '2010-01-01 00:00:00';
end if;
select stp_header_archive_max_date;

insert into STP_HEADER_ARCHIVE
(stpDate, id, resets, uptime, type, sequenceNumber, length, source, receiver, frequency, rx_location, receiver_rf, demodulator, measuredTCA, measuredTCAfrequency, date_time)
(select stpDate, id, resets, uptime, type, sequenceNumber, length, source, receiver, frequency, rx_location, receiver_rf, demodulator, measuredTCA, measuredTCAfrequency, date_time
from STP_HEADER
where STP_HEADER.date_time > stp_header_archive_max_date
and timestampdiff(DAY, date_time, stp_header_max_date) > 30);

delete from STP_HEADER_ARCHIVE_COUNT;

insert into STP_HEADER_ARCHIVE_COUNT
(id, receiver, duv_total, highspeed_total, psk_total)
(select id, receiver, 
sum(case when source like '%duv' then 1 else 0 end) duv_total, 
sum(case when source like '%highspeed' then 1 else 0 end) highspeed_total,
sum(case when source like '%bpsk' then 1 else 0 end) psk_total 
from STP_HEADER_ARCHIVE group by id, receiver);

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
