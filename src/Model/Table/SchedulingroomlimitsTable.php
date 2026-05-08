<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\RulesChecker;
use Cake\Validation\Validator;

class SchedulingroomlimitsTable extends Table {

    public function initialize(array $config): void {
        parent::initialize($config);
        $this->setTable('schedulingroomlimits');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }
}
