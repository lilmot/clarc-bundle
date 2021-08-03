<?php
/**
 * Some validation failed exception
 *
 * @author Artur Turchin <a.turchin@artox.com>
 */

declare(strict_types=1);

namespace ArtoxLab\Bundle\ClarcBundle\Core\Interfaces\Exceptions;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationFailedException extends InvalidArgumentException
{
    /**
     * Basic message
     *
     * @var string
     */
    protected $basicMessage = 'Validation error';

    /**
     * Validation errors
     *
     * @var array
     */
    private $validationErrors = [];

    /**
     * RequestValidationFailedException constructor.
     *
     * @param ConstraintViolationListInterface $violations Validation result
     */
    public function __construct(ConstraintViolationListInterface $violations)
    {
        parent::__construct($this->basicMessage, Response::HTTP_UNPROCESSABLE_ENTITY);

        if (count($violations) < 1) {
            return;
        }

        foreach ($violations as $violation) {
            $path = $this->formatPropertyPath($violation->getPropertyPath());

            $this->validationErrors[$path][] = $violation->getMessage();
        }
    }

    /**
     * Returns validation errors
     *
     * @return array
     */
    public function getValidationErrors() : array
    {
        return $this->validationErrors;
    }

    /**
     * Format property path with violations
     *
     * @param string $rawPath Raw property path
     *
     * @return string
     */
    private function formatPropertyPath(string $rawPath): string
    {
        preg_match_all('/(^[^\[;^\]]+)|(\[[^[]+])/', $rawPath, $matches);

        $groups = reset($matches);

        if ($groups === false) {
            $groups = [];
        }

        return array_reduce(
            $groups,
            static fn (string $path, string $part): string => (
                $path . '[' . trim($part, '[ ]') . ']'
            ),
            ''
        );
    }

}
