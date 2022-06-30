#
# Extend table 'sys_file'
#
CREATE TABLE sys_file (
	tx_oracledam_id char(36) DEFAULT NULL,
	tx_oracledam_version varchar(16) DEFAULT '' NOT NULL,
	tx_oracledam_file_timestamp int(11) unsigned DEFAULT 0 NOT NULL,
	tx_oracledam_metadata_timestamp int(11) unsigned DEFAULT 0 NOT NULL,

	KEY oracledam (tx_oracledam_id)
);
