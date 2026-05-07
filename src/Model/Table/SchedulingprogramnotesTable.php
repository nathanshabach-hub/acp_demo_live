<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class SchedulingprogramnotesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('schedulingprogramnotes');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }
}
