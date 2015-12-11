<?php
namespace Pipindex\Salesforce\Authentications;

use Omniphx\Forrest\Interfaces\StorageInterface;
use Omniphx\Forrest\Exceptions\MissingTokenException;
use Omniphx\Forrest\Exceptions\MissingRefreshTokenException;
use Omniphx\Forrest\Exceptions\MissingKeyException;
use Crypt;
use Log;
use Illuminate\Filesystem\Filesystem;

class FileStorage implements StorageInterface {

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new file driven handler instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem $files
     * @param  string $path
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->path = $path;
        $this->files = $files;
    }

	/**
	 * Store into session.
	 * @param $key
	 * @param $value
	 * @return void
	 */
	public function put($key, $value)
	{
        if ( ! is_array($key)) $arrayKey = array($key => $value);

        $data = json_encode($arrayKey);

        $this->files->put($this->path.'/' . $key, $data, true);
	}

	/**
	 * Get from session
	 * @param $key
	 * @return mixed
	 */
	public function get($key)
	{
        if ($this->files->exists($path = $this->path.'/'. $key))
        {
            $json = $this->files->get($path);
            $arrayData = json_decode($json, true);

            return $arrayData[$key];

        }

		throw new MissingKeyException(sprintf("No value for requested key: %s",$key));
	}

	/**
	 * Encrypt authentication token and store it in session.
	 * @param array $token
	 * @return void
	 */
	public function putToken($token)
	{
		$encryptedToken = Crypt::encrypt($token);
        $this->files->put($this->path.'/token', $encryptedToken, true);
	}

	/**
	 * Get token from the session and decrypt it.
	 * @return mixed
	 */
	public function getToken()
    {
        if ($this->files->exists($path = $this->path.'/token'))
        {
            $token = $this->files->get($path);
            return Crypt::decrypt($token);
        }

		throw new MissingTokenException(sprintf('No token available in current session'));
	}

	/**
	 * Encrypt refresh token and pass into session.
	 * @param  Array $token
	 * @return void
	 */
	public function putRefreshToken($token)
	{
        $encryptedToken = Crypt::encrypt($token);
        $this->files->put($this->path.'/refresh_token', $encryptedToken, true);
	}

	/**
	 * Get refresh token from session and decrypt it.
	 * @return mixed
	 */
	public function getRefreshToken()
	{
        if ($this->files->exists($path = $this->path.'/refresh_token'))
        {
            $token = $this->files->get($path);
            return Crypt::decrypt($token);
        }

		throw new MissingRefreshTokenException(sprintf('No refresh token stored in current session. Verify you have added refresh_token to your scope items on your connected app settings in Salesforce.'));
	}
}