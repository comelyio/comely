<?php
/**
 * This file is part of Comely package.
 * https://github.com/comelyio/comely
 *
 * Copyright (c) 2016-2018 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/comelyio/comely/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Comely\IO\HttpRouter\Authentication;

use Comely\IO\HttpRouter\Exception\AuthenticationException;
use Comely\IO\HttpRouter\Router\Authentication;

/**
 * Class BasicAuth
 * @package Comely\IO\HttpRouter\Authentication
 */
class BasicAuth extends Authentication
{
    /**
     * @throws AuthenticationException
     */
    public function authenticate(): void
    {
        try {
            $username = $this->sanitize($_SERVER["PHP_AUTH_USER"] ?? null);
            $password = $this->sanitize($_SERVER["PHP_AUTH_PW"] ?? null);

            // Sent PHP_AUTH_USER header?
            if (!$username) {
                throw new AuthenticationException(
                    sprintf('Authentication is required to enter this "%s"', $this->realm)
                );
            }

            // Authenticate
            try {
                /** @var null|User $user */
                $user = $this->users[$username] ?? null;
                if (!$user) {
                    throw new AuthenticationException('No such username was found');
                }

                if ($user->password !== $password) {
                    throw new AuthenticationException('Incorrect password');
                }
            } catch (AuthenticationException $e) {
                throw new AuthenticationException('Incorrect username or password');
            }

        } catch (AuthenticationException $e) {
            header(sprintf('WWW-Authenticate: Basic realm="%s"', $this->realm));
            header('HTTP/1.0 401 Unauthorized');

            // Callback method for unauthorized requests
            if (is_callable($this->unauthorized)) {
                call_user_func($this->unauthorized);
            }

            throw new AuthenticationException($e->getMessage());
        }
    }

    /**
     * @param $in
     * @return string
     */
    private function sanitize($in): string
    {
        if (!$in) {
            return "";
        }

        return filter_var(strval($in), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
    }
}