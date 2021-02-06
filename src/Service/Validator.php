<?php
/**
 * Scarawler Validator Service
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service;

use Scrawler\Scrawler;

class Validator extends \Rakit\Validation\Validator
{

    public function validateRequest($rules, $messages = [])
    {
        $validation = $this->make(Scrawler()->request() - all(), $rules);
        if (!empty($messages)) {
            $validation->setMessages($messages);
        }
        $validation->validate();
        return $validation;
    }
}
