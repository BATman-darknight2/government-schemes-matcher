USE government_schemes;
INSERT INTO categories(name,slug) VALUES ('Education','education'),('Livelihood','livelihood'),('Health and wellbeing','health-wellbeing');
INSERT INTO schemes(title,slug,summary,description,benefit_description,application_instructions,status,verification_status,is_demo)
VALUES
('DEMO ONLY: Student Learning Support','demo-student-learning-support','A fictional education support scheme for testing the matcher.','This record is invented for software demonstration. It is not a government programme.','Fictional one-time support amount shown for testing only.','No real application exists. Use this record only to demonstrate the software.','active','demo',1),
('DEMO ONLY: Rural Skills Starter','demo-rural-skills-starter','A fictional skills programme with an optional age condition.','This record is invented for software demonstration.','Fictional training support shown for testing only.','No real application exists.','active','demo',1),
('DEMO ONLY: Archived Health Support','demo-archived-health-support','Inactive example for admin status testing.','Fictional demonstration data.','No benefit is offered.','No application exists.','inactive','demo',1);
INSERT INTO scheme_categories(scheme_id,category_id)
SELECT s.id,c.id FROM schemes s JOIN categories c ON c.slug=CASE WHEN s.slug LIKE '%student%' THEN 'education' WHEN s.slug LIKE '%skills%' THEN 'livelihood' ELSE 'health-wellbeing' END;
INSERT INTO eligibility_rule_groups(scheme_id,group_key,label,condition_operator,position)
SELECT id,'main','All demonstration conditions','AND',0 FROM schemes WHERE slug='demo-student-learning-support';
INSERT INTO scheme_eligibility_rules(scheme_id,group_id,attribute_key,operator,expected_value,description,position)
SELECT s.id,g.id,'age','gte','18','Applicant must be at least 18 years old (fictional rule).',0 FROM schemes s JOIN eligibility_rule_groups g ON g.scheme_id=s.id WHERE s.slug='demo-student-learning-support';
INSERT INTO scheme_eligibility_rules(scheme_id,group_id,attribute_key,operator,expected_value,description,position)
SELECT s.id,g.id,'annual_income','lte','500000','Annual family income must not exceed ₹5,00,000 (fictional rule).',1 FROM schemes s JOIN eligibility_rule_groups g ON g.scheme_id=s.id WHERE s.slug='demo-student-learning-support';
INSERT INTO scheme_documents(scheme_id,name,description,mandatory,position)
SELECT id,'DEMO: Proof of enrolment','Fictional example only; do not upload a document.',1,0 FROM schemes WHERE slug='demo-student-learning-support';
INSERT INTO eligibility_rule_groups(scheme_id,group_key,label,condition_operator,position)
SELECT id,'main','Applicant is rural and from an allowed state','AND',0 FROM schemes WHERE slug='demo-rural-skills-starter';
INSERT INTO eligibility_rule_groups(scheme_id,group_key,label,condition_operator,position)
SELECT id,'occupation','Student or unemployed','OR',1 FROM schemes WHERE slug='demo-rural-skills-starter';
INSERT INTO scheme_eligibility_rules(scheme_id,group_id,attribute_key,operator,expected_value,description,position)
SELECT s.id,g.id,'residence_type','eq','rural','Applicant must live in a rural area (fictional rule).',0 FROM schemes s JOIN eligibility_rule_groups g ON g.scheme_id=s.id WHERE s.slug='demo-rural-skills-starter' AND g.group_key='main';
INSERT INTO scheme_eligibility_rules(scheme_id,group_id,attribute_key,operator,expected_value,description,position)
SELECT s.id,g.id,'state','in','Rajasthan|Haryana','Applicant must live in Rajasthan or Haryana (fictional rule).',1 FROM schemes s JOIN eligibility_rule_groups g ON g.scheme_id=s.id WHERE s.slug='demo-rural-skills-starter' AND g.group_key='main';
INSERT INTO scheme_eligibility_rules(scheme_id,group_id,attribute_key,operator,expected_value,description,position)
SELECT s.id,g.id,'occupation','eq','Student','Applicant must be a student (fictional option).',0 FROM schemes s JOIN eligibility_rule_groups g ON g.scheme_id=s.id WHERE s.slug='demo-rural-skills-starter' AND g.group_key='occupation';
INSERT INTO scheme_eligibility_rules(scheme_id,group_id,attribute_key,operator,expected_value,description,position)
SELECT s.id,g.id,'occupation','eq','Unemployed','Applicant may instead be unemployed (fictional option).',1 FROM schemes s JOIN eligibility_rule_groups g ON g.scheme_id=s.id WHERE s.slug='demo-rural-skills-starter' AND g.group_key='occupation';
INSERT INTO scheme_sources(scheme_id,source_name,source_url,verification_status)
SELECT id,'Demonstration record','https://example.invalid/demo','demo' FROM schemes WHERE is_demo=1;
