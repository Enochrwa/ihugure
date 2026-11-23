-- IHUGURE APP - SAMPLE DATA for a local / development database.
-- Run it AFTER schema.sql:   mysql -u ihugure -p ihugure < seed-sample.sql
-- Safe to run more than once (it skips what already exists). Do NOT rely on it for production content.
--
-- Why it exists: schema.sql creates empty tables. With no questions, a player who pays and presses
-- "start" has nothing to be asked (the app now shows a "no new questions" screen and writes a
-- NO_QUESTION line in game_logs). Only English questions are included; to offer another language, add
-- its row to `language` and translated questions/answers with the same inquiryid (see docs/I18N.md).

INSERT INTO language (title, code) SELECT 'English', 'en' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM language WHERE code = 'en');
SET @en = (SELECT id FROM language WHERE code = 'en' ORDER BY id LIMIT 1);

INSERT INTO category (title) SELECT 'General knowledge' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM category WHERE title = 'General knowledge');
INSERT INTO category (title) SELECT 'Sport' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM category WHERE title = 'Sport');
INSERT INTO category (title) SELECT 'Music' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM category WHERE title = 'Music');
INSERT INTO investment (amount) SELECT 300 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM investment WHERE amount = 300);
INSERT INTO investment (amount) SELECT 500 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM investment WHERE amount = 500);
INSERT INTO investment (amount) SELECT 1000 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM investment WHERE amount = 1000);

-- General knowledge
SET @cat = (SELECT id FROM category WHERE title = 'General knowledge' ORDER BY id LIMIT 1);
INSERT IGNORE INTO inquiry (id, title) VALUES (9001, 'What is the capital of Rwanda?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9001, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9001);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9001, 'What is the capital of Rwanda?', 9001, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT 'Kigali' title, 1 correct, 9001 iq, 9001 qid, @en lid UNION ALL SELECT 'Huye', 0, 9001, 9001, @en UNION ALL SELECT 'Musanze', 0, 9001, 9001, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9001);
INSERT IGNORE INTO inquiry (id, title) VALUES (9002, 'Which is the largest lake in Rwanda?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9002, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9002);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9002, 'Which is the largest lake in Rwanda?', 9002, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT 'Lake Kivu' title, 1 correct, 9002 iq, 9002 qid, @en lid UNION ALL SELECT 'Lake Muhazi', 0, 9002, 9002, @en UNION ALL SELECT 'Lake Ruhondo', 0, 9002, 9002, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9002);
INSERT IGNORE INTO inquiry (id, title) VALUES (9003, 'How many continents are there?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9003, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9003);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9003, 'How many continents are there?', 9003, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT '7' title, 1 correct, 9003 iq, 9003 qid, @en lid UNION ALL SELECT '5', 0, 9003, 9003, @en UNION ALL SELECT '6', 0, 9003, 9003, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9003);
INSERT IGNORE INTO inquiry (id, title) VALUES (9004, 'What is the chemical formula of water?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9004, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9004);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9004, 'What is the chemical formula of water?', 9004, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT 'H2O' title, 1 correct, 9004 iq, 9004 qid, @en lid UNION ALL SELECT 'CO2', 0, 9004, 9004, @en UNION ALL SELECT 'O2', 0, 9004, 9004, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9004);
INSERT IGNORE INTO inquiry (id, title) VALUES (9005, 'Which planet is known as the Red Planet?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9005, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9005);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9005, 'Which planet is known as the Red Planet?', 9005, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT 'Mars' title, 1 correct, 9005 iq, 9005 qid, @en lid UNION ALL SELECT 'Venus', 0, 9005, 9005, @en UNION ALL SELECT 'Jupiter', 0, 9005, 9005, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9005);

-- Sport
SET @cat = (SELECT id FROM category WHERE title = 'Sport' ORDER BY id LIMIT 1);
INSERT IGNORE INTO inquiry (id, title) VALUES (9006, 'How many players of one team are on the pitch in football?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9006, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9006);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9006, 'How many players of one team are on the pitch in football?', 9006, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT '11' title, 1 correct, 9006 iq, 9006 qid, @en lid UNION ALL SELECT '9', 0, 9006, 9006, @en UNION ALL SELECT '7', 0, 9006, 9006, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9006);
INSERT IGNORE INTO inquiry (id, title) VALUES (9007, 'Which sport is played with a shuttlecock?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9007, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9007);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9007, 'Which sport is played with a shuttlecock?', 9007, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT 'Badminton' title, 1 correct, 9007 iq, 9007 qid, @en lid UNION ALL SELECT 'Tennis', 0, 9007, 9007, @en UNION ALL SELECT 'Volleyball', 0, 9007, 9007, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9007);
INSERT IGNORE INTO inquiry (id, title) VALUES (9008, 'How many rings are on the Olympic flag?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9008, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9008);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9008, 'How many rings are on the Olympic flag?', 9008, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT '5' title, 1 correct, 9008 iq, 9008 qid, @en lid UNION ALL SELECT '4', 0, 9008, 9008, @en UNION ALL SELECT '6', 0, 9008, 9008, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9008);
INSERT IGNORE INTO inquiry (id, title) VALUES (9009, 'Which cycling race is Rwanda famous for?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9009, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9009);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9009, 'Which cycling race is Rwanda famous for?', 9009, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT 'Tour du Rwanda' title, 1 correct, 9009 iq, 9009 qid, @en lid UNION ALL SELECT 'Tour de France', 0, 9009, 9009, @en UNION ALL SELECT 'Giro d’Italia', 0, 9009, 9009, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9009);
INSERT IGNORE INTO inquiry (id, title) VALUES (9010, 'How many points is a basketball shot from behind the three-point line worth?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9010, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9010);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9010, 'How many points is a basketball shot from behind the three-point line worth?', 9010, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT '3' title, 1 correct, 9010 iq, 9010 qid, @en lid UNION ALL SELECT '2', 0, 9010, 9010, @en UNION ALL SELECT '4', 0, 9010, 9010, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9010);

-- Music
SET @cat = (SELECT id FROM category WHERE title = 'Music' ORDER BY id LIMIT 1);
INSERT IGNORE INTO inquiry (id, title) VALUES (9011, 'The inanga is what kind of instrument?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9011, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9011);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9011, 'The inanga is what kind of instrument?', 9011, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT 'A stringed instrument' title, 1 correct, 9011 iq, 9011 qid, @en lid UNION ALL SELECT 'A drum', 0, 9011, 9011, @en UNION ALL SELECT 'A flute', 0, 9011, 9011, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9011);
INSERT IGNORE INTO inquiry (id, title) VALUES (9012, 'How many strings does a standard guitar have?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9012, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9012);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9012, 'How many strings does a standard guitar have?', 9012, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT '6' title, 1 correct, 9012 iq, 9012 qid, @en lid UNION ALL SELECT '4', 0, 9012, 9012, @en UNION ALL SELECT '8', 0, 9012, 9012, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9012);
INSERT IGNORE INTO inquiry (id, title) VALUES (9013, 'Which instrument has black and white keys?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9013, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9013);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9013, 'Which instrument has black and white keys?', 9013, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT 'Piano' title, 1 correct, 9013 iq, 9013 qid, @en lid UNION ALL SELECT 'Drum', 0, 9013, 9013, @en UNION ALL SELECT 'Flute', 0, 9013, 9013, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9013);
INSERT IGNORE INTO inquiry (id, title) VALUES (9014, 'In Kinyarwanda, "ingoma" means which instrument?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9014, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9014);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9014, 'In Kinyarwanda, "ingoma" means which instrument?', 9014, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT 'Drum' title, 1 correct, 9014 iq, 9014 qid, @en lid UNION ALL SELECT 'Flute', 0, 9014, 9014, @en UNION ALL SELECT 'Guitar', 0, 9014, 9014, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9014);
INSERT IGNORE INTO inquiry (id, title) VALUES (9015, 'How many notes are in the basic do-re-mi scale?');
INSERT INTO inquirycategory (inquiryid, categoryid) SELECT 9015, @cat FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM inquirycategory WHERE inquiryid = 9015);
INSERT IGNORE INTO question (id, title, inquiryid, languageid) VALUES (9015, 'How many notes are in the basic do-re-mi scale?', 9015, @en);
INSERT INTO answer (title, correct, inquiryid, questionid, languageid)
  SELECT v.title, v.correct, v.iq, v.qid, v.lid FROM (SELECT '7' title, 1 correct, 9015 iq, 9015 qid, @en lid UNION ALL SELECT '5', 0, 9015, 9015, @en UNION ALL SELECT '9', 0, 9015, 9015, @en) v WHERE NOT EXISTS (SELECT 1 FROM answer WHERE questionid = 9015);
