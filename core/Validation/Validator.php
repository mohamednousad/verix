<?php
namespace Verix\Core\Validation;
final class Validator {
    public static function validate(array $data,array $rules): array {
        $errors=[];
        foreach($rules as $field=>$ruleString){
            $value=$data[$field] ?? null;
            foreach(explode('|',$ruleString) as $rule){
                [$name,$arg]=array_pad(explode(':',$rule,2),2,null);
                if($name==='required' && ($value===null || $value==='')) $errors[$field][]='The field is required.';
                if(($value===null || $value==='') && $name!=='required') continue;
                if($name==='email' && !filter_var($value,FILTER_VALIDATE_EMAIL)) $errors[$field][]='Must be a valid email.';
                if($name==='min' && mb_strlen((string)$value)<(int)$arg) $errors[$field][]="Minimum length is {$arg}.";
                if($name==='max' && mb_strlen((string)$value)>(int)$arg) $errors[$field][]="Maximum length is {$arg}.";
                if($name==='integer' && filter_var($value,FILTER_VALIDATE_INT)===false) $errors[$field][]='Must be an integer.';
                if($name==='boolean' && !in_array($value,[true,false,0,1,'0','1'],true)) $errors[$field][]='Must be boolean.';
            }
        }
        return $errors;
    }
}
