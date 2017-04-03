<?php

namespace Sjoerdmaessen\PDODebug;

/**
 * Used for debugging
 */
class Statement extends \PDOStatement
{
	/**
	 * Contains our columns and mapped values
	 * @var array
	 */
	protected $boundParameters = array();

	/**
	 * @param null $input_parameters
	 * @return bool
	 */
	public function execute(array $input_parameters = null)
	{
		if($input_parameters) {
			foreach($input_parameters as $name => $value) {
				$this->bindParam(':'.$name, $value);
			}
		}

		return parent::execute($input_parameters);
	}

	/**
	 * Binds a value to a specific variable
	 *
	 * @param string $parameter
	 * @param mixed $variable
	 * @param int $dataType
	 * @return TRUE on success or FALSE on failure
	 */
	public function bindValue($parameter, $variable, $dataType = PDO::PARAM_STR)
	{
		$this->bind($parameter, $variable, $dataType);
		return parent::bindValue($parameter, $variable, $dataType);
	}

	/**
	 * Binds a parameter to the specified variable name and saved the assigned variable
	 *
	 * @param string $parameterName
	 * @param mixed $variable
	 * @param int $dataType
	 * @param int $length
	 * @param mixed $driverOptions
	 * @return TRUE on success or FALSE on failure
	 */
	public function bindParam($parameterName, &$variable, $dataType = PDO::PARAM_STR, $length = null, $driverOptions = null)
	{
		$this->bind($parameterName, $variable, $dataType);
		return parent::bindParam($parameterName, $variable, $dataType, $length, $driverOptions);
	}

	/**
	 * Return the prepared query with the bound parameter values
	 *
	 * @return string
	 */
	public function getQuery()
	{
		$boundParameters = $this->getBoundParameters();
		if (empty($boundParameters)) {
			trigger_error(sprintf('The method "%s" is used to get the prepared query with the bound parameters, no named parameters are bound yet', __FUNCTION__));
		}
		uasort($boundParameters, array($this, 'sortBoundParamenters'));

		return str_replace(array_keys($boundParameters), array_values($boundParameters), $this->queryString);
	}

	/**
	 * Tries to give the reason of the error
	 *
	 * @return string
	 */
	public function getFormattedErrorInfo()
	{
		$query = $this->getQuery();
		$error = $this->errorInfo();

		// Check the error code
		if ($error[0] === '00000') {
			return 'No errors occured';
		}

		// Append error code
		$errorMessage = '<strong>Error code ' . $error[0] . ':</strong><pre> ' . $error[2] . '</pre>';

		// Append query
		$errorMessage .= '<strong>Executed query:</strong><pre>' . $query . '</pre>';

		// Append possible solution, check missing ","
		preg_match_all('/at line (?P<lineNumber>\d+)/', $error[2], $aMatches);
		if (isset($aMatches['lineNumber'])) {
			$lineInfo = array_slice(explode(PHP_EOL, $query), 4, 1);
			$lineProblem = array_pop($lineInfo);

			// Check if we are missing a ","
			if (strrchr($errorMessage, ',') !== strlen($lineProblem)) {
				$errorMessage .= '<strong>Possible solution:</strong><pre>Add a "," at the end of line: "' . trim($lineProblem) . '"</pre>';
			} else {
				$errorMessage .= '<strong>The error should exists in this line:</strong><pre>' . $query . '</pre>';
			}
		}

		return $errorMessage;
	}

	/**
	 * Sort the bound parameters by there length, the items with the longest length will appear first
	 * we need to sort our items to prevent smaller parameters to replace parts of bigger parameters
	 *
	 * @param array $first
	 * @param array $second
	 * @return int
	 */
	protected function sortBoundParamenters($first, $second)
	{
		return strlen($first) - strlen($second);
	}

	/**
	 * Returns all bound parameters
	 * @return array
	 */
	public function getBoundParameters()
	{
		return $this->boundParameters;
	}

	/**
	 * Bind a parameter to a specific value
	 *
	 * @param string $parameter
	 * @param mixed $variable
	 * @param int $dataType
	 * @return null
	 */
	protected function bind($parameter, $variable, $dataType)
	{
		switch ($dataType) {
			case \PDO::PARAM_BOOL:
			case \PDO::PARAM_INT:
				$variable = empty($variable) ? 'NULL' : (int) $variable;
				$this->boundParameters[$parameter] = $variable;
				break;
			case \PDO::PARAM_NULL:
			case ($variable === null):
				$this->boundParameters[$parameter] = 'NULL';
				break;
			case \PDO::PARAM_STR:
				$this->boundParameters[$parameter] = '"' . (string) $variable . '"';
				break;
			default:
				trigger_error(sprintf('The data type "%s" is currently not supported by %s', $dataType, __CLASS__), E_USER_NOTICE);
				break;
		}
	}
}

?>