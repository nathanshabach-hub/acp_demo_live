-- ============================================================
-- Scheduling Event Tweaks
-- Run this SQL on the live database before using the Tweaks UI
-- ============================================================

-- Per-event tweaks per convention season
-- A: pinned_day        – restrict event to a specific day of the week
-- B: pinned_room_id    – force a specific room (overrides auto room assignment)
-- C: pinned_start_time – force a specific start time for this event's block
-- D: available_from_time / available_to_time – event-level availability window
-- (D and E handled below via conventionrooms and editschedulingtimings)

CREATE TABLE IF NOT EXISTS `schedulingeventtweaks` (
  `id`                  bigint(20)   NOT NULL AUTO_INCREMENT,
  `conventionseasons_id` int(10)     DEFAULT NULL,
  `event_id`            int(10)      DEFAULT NULL,
  `pinned_day`          varchar(50)  DEFAULT NULL  COMMENT 'A: Day to schedule on (e.g. Monday)',
  `pinned_start_time`   time         DEFAULT NULL  COMMENT 'C: Override start time for this event block',
  `available_from_time` time         DEFAULT NULL  COMMENT 'D: Event can only start from this time',
  `available_to_time`   time         DEFAULT NULL  COMMENT 'D: Event must finish by this time',
  `pinned_room_id`      int(10)      DEFAULT NULL  COMMENT 'B: Force specific room_id',
  `created`             datetime     DEFAULT NULL,
  `modified`            datetime     DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cs_event` (`conventionseasons_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;


-- D: Room-level availability window (limits when a room can be used)
-- Note: MySQL does not support ADD COLUMN IF NOT EXISTS (MariaDB only).
-- Run only if these columns do not already exist on conventionrooms.
ALTER TABLE `conventionrooms`
  ADD COLUMN `available_from` time DEFAULT NULL COMMENT 'D: Room available from (blank = use convention start)',
  ADD COLUMN `available_to`   time DEFAULT NULL COMMENT 'D: Room available to (blank = use convention finish)';


-- Event-level availability window per convention season
-- Run if these columns do not already exist.
ALTER TABLE `schedulingeventtweaks`
  ADD COLUMN `available_from_time` time DEFAULT NULL COMMENT 'D: Event can only start from this time',
  ADD COLUMN `available_to_time`   time DEFAULT NULL COMMENT 'D: Event must finish by this time';
