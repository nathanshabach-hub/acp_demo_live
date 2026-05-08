<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class JudgingAssignmentsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setTable('judging_assignments');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }
}
