<?php

/**
 * A Simple Socket Client for PHP 5.2+
 * 
 * This class implements a generic socket client. It can be extended to
 * create client libraries working with text-based protocols.
 * In addition to socket creation, reading, and writing capabilities,
 * this class also supports lazy loading (not connect until actually needed),
 * as well as basic key validation and command building helper methods.
 * 
 * URL: http://github.com/kijin/simplesocket
 * Version: 0.1.8
 * 
 * Copyright (c) 2010-2011, Kijin Sung <kijin.sung@gmail.com>
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class SimpleSocketClient
{
    /**
     * Connection settings.
     * 
     * Although these properties can be manipulated directly by children,
     * it is best to keep them in the hands of the parent class.
     */
    
    protected $_con = null;
    protected $_host = '';
    protected $_port = 0;
    protected $_timeout = 0;
    
    
    /**
     * Default host and port.
     *
     * These properties can be overridden by children to provide default values
     * for host and port if they're not passed to the constructor.
     */
    
    protected $_default_host = null;
    protected $_default_port = null;
    
    
    /**
     * Constructor.
     * 
     * Host and port must be supplied at the time of instantiation.
     * 
     * @param  string  The hostname, IP address, or UNIX socket for the server.
     * @param  int     The port of the server, or false for UNIX sockets.
     * @param  int     Connection timeout in seconds. [optional: default is 5]
     */
    
    public function __construct($host = null, $port = null, $timeout = 5)
    {
        // A quick check for IPv6 addresses. (They contain colons.)
        
        if (strpos($host, ':') !== false && strpos($host, '[') === false)
        {
            $host = '[' . $host . ']';
        }
        
        // Use default values?
        
        if (is_null($host) && is_null($port))
        {
            $host = $this->_default_host;
            $port = $this->_default_port;
        }
        
        // Keep the connection info, but don't connect now.
        
        $this->_host = $host;
        $this->_port = $port;
        $this->_timeout = $timeout;
    }
    
    
    /**
     * Connect to the server.
     * 
     * Normally, this method is useful only for debugging purposes, because
     * it will be called automatically the first time a read/write operation
     * is attempted.
     */
    
    public function connect()
    {
        // If already connected, don't do anything.
        
        if ($this->_con !== null && $this->_con !== false) return true;
        
        // If a previous connection attempt had failed, do not retry.
        
        if ($this->_con === false) throw new SimpleSocketException('Cannot connect to ' . $this->_host . ' port ' . $this->_port);
        
        // Attempt to connect.
        
        $socket = $this->_port ? ($this->_host . ':' . $this->_port) : ('unix://' . $this->_host);
        $this->_con = stream_socket_client($socket, $errno, $errstr, $this->_timeout);
        
        // If there's an error, set $_con to false, and throw an exception.
        
        if (!$this->_con)
        {
            $this->_con = false;
            throw new SimpleSocketException('Cannot connect to ' . $this->_host . ' port ' . $this->_port . ': ' . $errstr . ' (code ' . $errno . ')');
        }
        
        // Return true to indicate success.
        
        return true;
    }
    
    
    /**
     * Disconnect from the server.
     * 
     * Normally, this method is useful only for debugging purposes, because
     * it will be automatically called in the event of an error (resulting in
     * reconnection the next time a read/write operation is attempted), as
     * well as at the end of the execution of the script.
     */
    
    public function disconnect()
    {
        // Close the socket.
        
        @fclose($this->_con);
        $this->_con = null;
        
        // Return true to indicate success.
        
        return true;
    }
    
    
    /**
     * Generic read method.
     * 
     * This method reads a specified number of bytes from the socket.
     * By default, it will also read a CRLF sequence (2 bytes) in addition to
     * the specified number of bytes, and remove that CRLF sequence once it
     * has been read. This is useful for most text-based protocols; however,
     * if you do not want such behavior, pass additional 'false' arguments.
     * 
     * @param   int        The number of bytes to read, or -1 to read until EOF.
     * @param   bool       Whether or not to read CRLF at the end, too. [optional: default is true]
     * @return  string     Data read from the socket.
     * @throws  Exception  If an error occurs while reading from the socket.
     */
    
    public function read($bytes = -1, $autonewline = true)
    {
        // If not connected yet, connect now.
        
        if ($this->_con === null) $this->connect();
        
        // Read the data from the socket.
        
        $data = stream_get_contents($this->_con, $bytes);
        
        // If $autonewline is true, read 2 more bytes.
        
        if ($autonewline && $bytes !== -1) stream_get_contents($this->_con, 2);
        
        // If the result is false, throw an exception.
        
        if ($data === false)
        {
            $this->disconnect();
            throw new SimpleSocketException('Cannot read ' . $bytes . ' bytes from ' . $this->_host . ' port ' . $this->_port);
        }
        
        // Otherwise, return the data.
        
        return $data;
    }
    
    
    /**
     * Generic readline method.
     * 
     * This method reads one line from the socket, i.e. it reads until it hits
     * a CRLF sequence. By default, that CRLF sequence will be removed from the
     * return value. This is useful for most text-based protocols; however,
     * if you do not want such behavior, pass an additional 'false' argument.
     * 
     * @param   bool       Whether or not to strip CRLF from the end of the response. [optional: default is true]
     * @return  string     Data read from the socket.
     * @throws  Exception  If an error occurs while reading from the socket.
     */
        
    public function readline($trim = true)
    {
        // If not connected yet, connect now.
        
        if ($this->_con === null) $this->connect();
        
        // Read a line from the socket.
        
        $data = fgets($this->_con);
        
        // If the result is false, throw an exception.
        
        if ($data === false)
        {
            $this->disconnect();
            throw new SimpleSocketException('Cannot read a line from ' . $this->_host . ' port ' . $this->_port);
        }
        
        // Otherwise, trim and return the data.
        
        if ($trim && substr($data, strlen($data) - 2) === "\r\n") $data = substr($data, 0, strlen($data) - 2);
        return $data;
    }
    
    
    /**
     * Generic write method.
     * 
     * This method writes a string to the socket. By default, this method will
     * write a CRLF sequence in addition to the given string. This is useful
     * for most text-based protocols; however, if you do not want such behavior,
     * make sure to pass an additional 'false' argument.
     * 
     * @param   string     The string to write to the socket.
     * @param   bool       Whether or write CRLF in addition to the given string. [optional: default is true]
     * @return  bool       True on success.
     * @throws  Exception  If an error occurs while reading from the socket.
     */
    
    public function write($string, $autonewline = true)
    {
        // If not connected yet, connect now.
        
        if ($this->_con === null) $this->connect();
        
        // If $autonewline is true, add CRLF to the content.
        
        if ($autonewline) $string .= "\r\n";
        
        // Write the whole string to the socket.
        
        while ($string !== '')
        {
            // Start writing.
            
            $written = fwrite($this->_con, $string);
            
            // If the result is false, throw an exception.
            
            if ($written === false)
            {
                $this->disconnect();
                throw new SimpleSocketException('Cannot write to ' . $this->_host . ' port ' . $this->_port);
            }
            
            // If nothing was written, it probably means we've already done writing.
            
            if ($written == 0) return true;
            
            // Prepare the string for the next write.
            
            $string = substr($string, $written);
        }
        
        // Return true to indicate success.
        
        return true;
    }
    
    
    /**
     * Generic key validation method.
     * 
     * This method will throw an exception if:
     *   - The key is empty.
     *   - The key is more than 250 bytes long.
     *   - The key contains characters outside of the ASCII printable range.
     * 
     * @param   string     The key to validate.
     * @return  bool       True of the key is valid.
     * @throws  Exception  If the key is invalid.
     */
    
    public function validate_key($key)
    {
        if ($key === '') throw new InvalidKeyException('Key is empty');
        if (strlen($key) > 250) throw new InvalidKeyException('Key is too long: ' . $key);
        if (preg_match('/[^\\x21-\\x7e]/', $key)) throw new InvalidKeyException('Illegal character in key: ' . $key);
        return true;
    }
    
    
    /**
     * Generic command building method.
     * 
     * This method will accept one or more string arguments, and return them
     * all concatenated with one space between each. If this is convenient
     * for you, help yourself.
     * 
     * @param   string  As many arguments as you wish.
     * @return  string  The concatenated string.
     */
    
    public function build_command( /* arguments */ )
    {
        $args = func_get_args();
        return implode(' ' , $args);
    }
    
    
    /**
     * Destructor.
     * 
     * Although not really necessary, the destructor will attempt to
     * disconnect in case something weird happens.
     */
    
    public function __destruct()
    {
        @fclose($this->_con);
    }
}


/**
 * Exception class.
 */

class SimpleSocketException extends Exception { }
class InvalidKeyException extends SimpleSocketException { }
