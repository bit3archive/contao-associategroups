-- drop the procedure if exists
DROP PROCEDURE IF EXISTS associategroups_user_to_group $$

-- (re)create the update procedure
CREATE PROCEDURE associategroups_user_to_group (IN P_TSTAMP INT, IN P_USER_ID INT, IN P_GROUP_ARRAY BLOB)
  BEGIN
    -- clear the association table
	DELETE FROM tl_user_to_group
	  WHERE user_id=P_USER_ID
	    AND group_id NOT IN (SELECT id FROM tl_user_group
	                         WHERE P_GROUP_ARRAY REGEXP CONCAT('s:[0-9]*:"', id, '";')
                                OR P_GROUP_ARRAY REGEXP CONCAT('i:', id, ';'));

    -- insert new association
    INSERT INTO tl_user_to_group (tstamp, user_id, group_id)
      SELECT P_TSTAMP, P_USER_ID, id FROM tl_user_group
        WHERE (  P_GROUP_ARRAY REGEXP CONCAT('s:[0-9]*:"', id, '";')
              OR P_GROUP_ARRAY REGEXP CONCAT('i:', id, ';'))
           AND id NOT IN (SELECT group_id FROM tl_user_to_group WHERE user_id=P_USER_ID);
  END;
$$

-- drop the insert trigger
DROP TRIGGER IF EXISTS associategroups_user_to_group_insert $$

-- (re)create the insert trigger
CREATE TRIGGER associategroups_user_to_group_insert AFTER INSERT ON tl_user
  FOR EACH ROW BEGIN
    CALL associategroups_user_to_group(NEW.tstamp, NEW.id, NEW.groups);
  END;
$$

-- drop the update trigger
DROP TRIGGER IF EXISTS associategroups_user_to_group_update $$

-- (re)create the update trigger
CREATE TRIGGER associategroups_user_to_group_update AFTER UPDATE ON tl_user
  FOR EACH ROW BEGIN
    CALL associategroups_user_to_group(NEW.tstamp, NEW.id, NEW.groups);
  END;
$$

-- drop the delete trigger
DROP TRIGGER IF EXISTS associategroups_user_to_group_delete $$

-- (re)create the delete trigger
CREATE TRIGGER associategroups_user_to_group_delete BEFORE DELETE ON tl_user
  FOR EACH ROW BEGIN
    -- clear the association table
	DELETE FROM tl_user_to_group WHERE user_id=OLD.id;
  END;
$$

-- drop the group delete trigger
DROP TRIGGER IF EXISTS associategroups_user_group_delete $$

-- (re)create the delete trigger
CREATE TRIGGER associategroups_user_group_delete BEFORE DELETE ON tl_user_group
  FOR EACH ROW BEGIN
    -- clear the association table
	DELETE FROM tl_user_to_group WHERE group_id=OLD.id;
  END;
$$
