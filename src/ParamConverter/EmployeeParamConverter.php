<?php 

namespace Drupal\employee\ParamConverter;
 
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;
use Drupal\employee\EmployeeStorage;
use Drupal;

class EmployeeParamConverter implements ParamConverterInterface {
  
  public function convert($value, $definition, $name, array $defaults) {
  	if(!EmployeeStorage::exists($value)){
    	return 'invalid';
  	}
    return EmployeeStorage::load($value);
  }
 
  public function applies($definition, $name, Route $route) {
    return (!empty($definition['type']) && $definition['type'] == 'employee');
  }
}