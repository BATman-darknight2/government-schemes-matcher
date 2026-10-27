<?php
declare(strict_types=1);
require_once __DIR__.'/Database.php';
require_once __DIR__.'/EligibilityEngine.php';
$appConfig = require dirname(__DIR__).'/config/app.php';
ini_set('display_errors','0'); ini_set('log_errors','1'); error_reporting(E_ALL);
date_default_timezone_set($appConfig['timezone']);
ini_set('session.use_strict_mode','1');
session_name($appConfig['session_name']);
session_set_cookie_params(['httponly'=>true,'secure'=>(bool)$appConfig['https_only'],'samesite'=>'Lax','path'=>'/']);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
function db(): PDO { return Database::connection(); }
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $page, array $params=[]): string { global $appConfig; return rtrim((string)($appConfig['base_path']??''),'/').'/index.php?'.http_build_query(['page'=>$page]+$params); }
function redirect(string $page, array $params=[]): never { header('Location: '.url($page,$params)); exit; }
function csrf_token(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_check(): void { if($_SERVER['REQUEST_METHOD']!=='POST'||!hash_equals(csrf_token(),(string)($_POST['_csrf']??''))) { http_response_code(419); exit('This form expired. Go back, refresh, and try again.'); } }
function flash(string $message, string $type='success'): void { $_SESSION['flash']=['message'=>$message,'type'=>$type]; }
function current_user(): ?array { return $_SESSION['user']??null; }
function require_user(): array { $u=current_user(); if(!$u) redirect('login'); return $u; }
function require_admin(): array { $u=require_user(); if($u['role']!=='admin'){http_response_code(403);exit('Administrator access is required.');} return $u; }
function audit(string $action,string $type,?int $id=null): void { $s=db()->prepare('INSERT INTO audit_logs(admin_id,action,entity_type,entity_id) VALUES(?,?,?,?)');$s->execute([current_user()['id']??null,$action,$type,$id]); }
function render(string $view,array $data=[]): void { global $appConfig; $viewFile=dirname(__DIR__).'/app/Views/'.$view.'.php'; extract($data,EXTR_SKIP); require dirname(__DIR__).'/app/Views/layout.php'; }
