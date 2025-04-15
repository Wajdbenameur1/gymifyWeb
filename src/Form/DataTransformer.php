<?php 
namespace App\Form\DataTransformer;

use App\Enum\Role;
use Symfony\Component\Form\DataTransformerInterface;

class RoleDataTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        // If the value is already a Role enum, return it directly.
        if ($value instanceof Role) {
            return $value->value;
        }
        return null;
    }

    public function reverseTransform($value)
    {
        // Convert the string value back into the Role enum
        if (null === $value) {
            return null;
        }

        return Role::from($value); // Convert the string to the Role enum
    }
}
