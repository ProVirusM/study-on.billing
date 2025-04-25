<?php
// src/Dto/UserDto.php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    /**
     * @Assert\NotBlank(message="Email is mandatory")
     * @Assert\Email(message="Invalid email address")
     */
    #[Assert\NotBlank(message: 'Email is mandatory')]
    #[Assert\Email(message:"Invalid email address")]
    public string $username;

    /**
     * @Assert\NotBlank(message="Password is mandatory")
     * @Assert\Length(min=6, minMessage="Password must be at least 6 characters long")
     */
    #[Assert\NotBlank(message: 'Password is mandatory')]
    #[Assert\Length(min: 6, minMessage:"Password must be at least 6 characters long")]
    public string $password;
}