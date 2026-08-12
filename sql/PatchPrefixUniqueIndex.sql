--
-- Migration for the IDProvider extension
--
-- Adds a UNIQUE index on idprovider_increments.prefix. Run after
-- PatchPrefixField.sql and after any duplicate prefix rows have been merged
-- (see Hooks::onLoadExtensionSchemaUpdates).
--

CREATE UNIQUE INDEX /*i*/idprovider_increments_prefix ON /*_*/idprovider_increments (prefix);
