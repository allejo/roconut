<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Form\DataTransformer;

use AppBundle\Service\MessageLogTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Data transformer for the MessageLogTransformer bitwise flags to be used in our database and Forms.
 */
class MessageFilterBitwiseTransformer implements DataTransformerInterface
{
    private $em;

    /**
     * Constructor.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * This method is called when an integer representation of the bitwise flags is given to us by the database.
     *
     * @param int $value The calculated OR's for MessageLogTransformer bitwise filters
     *
     * @return array an array of integer values for each separate bitwise flag
     */
    public function transform($value)
    {
        $result = [];
        $filters = [
            MessageLogTransformer::HIDE_SERVER_MSG,
            MessageLogTransformer::HIDE_PRIVATE_MSG,
            MessageLogTransformer::HIDE_TEAM_CHAT,
            MessageLogTransformer::HIDE_ADMIN_CHAT,
            MessageLogTransformer::HIDE_JOIN_PART,
            MessageLogTransformer::HIDE_IP_ADDRESS,
            MessageLogTransformer::HIDE_KILL_MSG,
            MessageLogTransformer::HIDE_FLAG_ACTION,
            MessageLogTransformer::HIDE_PUBLIC_MSG,
            MessageLogTransformer::HIDE_PAUSING,
            MessageLogTransformer::HIDE_CLIENT_MSG,
            MessageLogTransformer::HIDE_TIMESTAMPS,
        ];

        foreach ($filters as $filter) {
            if ($value & $filter) {
                $result[] = $filter;
            }
        }

        return $result;
    }

    /**
     * This method is called when an array of integer values of bitwise flags is given to use by a Form.
     *
     * @param array $value An array of MessageLogTransformer bitwise filters
     *
     * @return int an OR'd value of all of the bitwise flags to be stored in the database
     */
    public function reverseTransform($value)
    {
        return (int) array_reduce($value, function ($a, $b) { return $a | $b; });
    }
}
