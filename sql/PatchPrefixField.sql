--
-- Migration for the IDProvider extension
--
-- Changes idprovider_increments.prefix from an unbounded, nullable "text"
-- column to a bounded, NOT NULL "varbinary(255)" column so a UNIQUE index
-- can be created on it (see PatchPrefixUniqueIndex.sql). This closes a race
-- condition where concurrent first-use of the same prefix could otherwise
-- insert duplicate rows.
--

UPDATE /*_*/idprovider_increments SET prefix = '' WHERE prefix IS NULL;

ALTER TABLE /*_*/idprovider_increments
    MODIFY COLUMN prefix varbinary(255) NOT NULL DEFAULT '';
