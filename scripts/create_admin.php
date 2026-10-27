<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__).'/app/Database.php';
if ($argc !== 3) { fwrite(STDERR,"Usage: php scripts/create_admin.php <email> <full-name>\n"); exit(2); }
$email=strtolower(trim($argv[1]));$name=trim($argv[2]);
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($name)<2||strlen($name)>120){fwrite(STDERR,"Provide a valid email and a name between 2 and 120 characters.\n");exit(2);}
fwrite(STDOUT,'New admin password (input visible): ');$password=trim((string)fgets(STDIN));
if(strlen($password)<10||!preg_match('/[A-Za-z]/',$password)||!preg_match('/\d/',$password)){fwrite(STDERR,"Password must be at least 10 characters and include a letter and a number.\n");exit(2);}
try{$pdo=Database::connection();$stmt=$pdo->prepare("INSERT INTO users(name,email,password_hash,role,accepted_terms_at) VALUES(?,?,?,'admin',NOW())");$stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);$pdo->prepare('INSERT INTO user_profiles(user_id) VALUES(?)')->execute([$pdo->lastInsertId()]);fwrite(STDOUT,"Administrator account created.\n");}catch(Throwable $e){fwrite(STDERR,"Could not create administrator: ".$e->getMessage()."\n");exit(1);}
