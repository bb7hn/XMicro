<?php

    namespace XMicro;

    require_once 'functions.php';
    require_once 'MySql.php';

    /**
     * A class that provides LightWeight `Micro Service` operations for a Rest API.
     */
    class MicroService
    {
        protected bool $debugger;

        /**
         * Constructor method that sets the response content type header and defines the DOMAIN constant.
         */
        public function __construct(bool $debugger = false)
        {
            if (!$debugger) {
                header('Content-type: application/json; charset=utf-8');
            } else {
                echo '
                    <style>
                    html{
                        background:rgba(0,0,0,.88);
                        color: #FFF;
                    }

                    body{
                        height: 100vh;
                        max-width:100vw;
                        overflow-y: auto;
                        padding: 24px;
                    }
                    
                    
                    
                </style>
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/github-dark.min.css" integrity="sha512-rO+olRTkcf304DQBxSWxln8JXCzTHlKnIdnMUwYvQa9/Jd4cQaNkItIUj6Z4nvW1dqK0SKXLbn9h4KwZTNtAyw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
                ';
            }

            define('DOMAIN', $this->get_server());
            $this->debugger = $debugger;
        }

        /**
         * Sends a response in JSON format with the specified message and HTTP status code.
         *
         * @param array $MessageArray An associative array containing the message and data to be sent in the response.
         * @param int $Code The HTTP status code to be sent in the response.
         */
        public function response(array $MessageArray = ['message' => 'OK', 'data' => null], int $Code = 200): void
        {
            response($MessageArray, $Code);
        }

        /**
         * Creates a new instance of the MySql class and returns it.
         *
         * @param string $DatabaseServer The server name or IP address for the MySQL server.
         * @param string $DatabaseName The name of the MySQL database to connect to.
         * @param string $DatabaseUser The username to use when connecting to the MySQL server.
         * @param string $DatabasePassword The password to use when connecting to the MySQL server.
         * @return MySql An instance of the MySql class.
         */
        public function conn_mysql(string $DatabaseServer, string $DatabaseName, string $DatabaseUser, string $DatabasePassword): MySql
        {
            return new MySql($DatabaseServer, $DatabaseName, $DatabaseUser, $DatabasePassword, $this->debugger);
        }

        /**
         * Retrieves the current server's base URL.
         *
         * @return string The current server's base URL.
         */
        protected function get_server(): string
        {
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $url = 'https://';
            } else {
                $url = 'http://';
            }
            // Append the host(domain name, ip) to the URL.
            $url .= $_SERVER['HTTP_HOST'];

            // Append the requested resource location to the URL
            /* $url .= $_SERVER['REQUEST_URI']; */

            return $url . '/';
        }

        public function __destruct()
        {
            // if debugger enabled run sql syntax highlighterz"
            if ($this->debugger) {
                echo '
                <script>hljs.highlightAll();</script>
            ';
            }
        }
    }
