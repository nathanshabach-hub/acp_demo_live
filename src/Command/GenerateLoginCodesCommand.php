<?php
namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\Command;
use Cake\Datasource\FactoryLocator;

class GenerateLoginCodesCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $usersTable = FactoryLocator::get('Table')->get('Users');
        $students = $usersTable->find('all', [
            'conditions' => ['Users.customer_code IS' => null, 'Users.user_type' => 'Student']
        ]);

        foreach ($students as $student) {
            do {
                $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                $studentCode = 'STU' . substr(str_shuffle(str_repeat($chars, 5)), 0, 5);
                $codeExists = $usersTable->find()->where(['Users.customer_code' => $studentCode])->first();
            } while ($codeExists);

            $student->customer_code = $studentCode;
            $usersTable->save($student);
        }

        $io->out('Login codes generated for all students without codes.');
    }
}