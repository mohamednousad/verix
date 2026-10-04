<?php
namespace Verix\Core\Database;
abstract class Model { protected QueryBuilder $db; public function __construct(){ $this->db=new QueryBuilder(); } }
