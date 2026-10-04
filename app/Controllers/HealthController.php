<?php
namespace App\Controllers;
use Verix\Core\Http\Request;
final class HealthController { public function index(Request $r): array { return ['framework'=>'Verix','status'=>'ok','time'=>gmdate('c')]; } }
