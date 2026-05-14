<?php
namespace App\Mailer;

use Cake\Mailer\Email;

/**
 * Concrete app mailer.
 *
 * In the older CakePHP version running in the Docker container:
 *   - Cake\Mailer\Mailer is abstract.
 *   - Cake\Mailer\Mailer::send($action, ...) requires an $action argument,
 *     incompatible with the way our controllers chain
 *     ->setTo()->setCc()->...->send().
 *
 * Cake\Mailer\Email is concrete in both old and current CakePHP (still ships
 * in 4.6.x as deprecated) and exposes the no-arg ->send() instance method
 * the controllers expect.
 */
class AppMailer extends Email
{
}
