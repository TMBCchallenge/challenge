<?php
require_once "Base.php";

class Comments extends Base
{
	protected $table = 'comments';

    const MAX_REPLIES = 2;

    public $rules = [
    	'parent_id' => 'integer',
    	'name' => 'required|string',
    	'comment' => 'required'
    ];

    public function getByParentId($parentId = 0)
    {
    	$this->sql = "SELECT * FROM {$this->table} WHERE parent_id = ? ORDER BY create_date DESC";
    	array_push($this->params, $parentId);

    	// prepare
    	list($result, $count) = $this->prepare();

    	return ($count) ? $result->fetchAll(PDO::FETCH_CLASS) : array();
    }

    public function getParentIdRowCount($parentId = 0)
    {
    	$this->sql = "SELECT COUNT(*) FROM {$this->table} WHERE parent_id = ?";
    	$this->params[] = $parentId;

    	// prepare
    	return $this->prepareCount();
    }

    public function getById($id)
    {
        $this->sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $this->params[] = $id;

        list($result, $count) = $this->prepare();

        return ($count) ? $result->fetchAll(PDO::FETCH_CLASS) : array();
    }

    public function tryInsert(object $comment)
    {
		// set some dedaults
    	if (!isset($comment->parent_id)) {
    		$comment->parent_id = 0;
    	}
    	// first we validate and make sure we have everything we need
    	$errors = $this->validate($comment);

    	if ($errors) {
    		return [false, $errors, null];
    	}

    	// before we continue we need to pull the count for this parent_id to make sure we dont go more than 2 level
    	if ($comment->parent_id > 0) {
    		if ($this->getParentIdRowCount($comment->parent_id) >= self::MAX_REPLIES) {
    			return [false, ['Insertion failed. We already reached max nested replies/comments'], null];
    		}

    		$this->insertFields($comment);

	    	//print_r($this->sql);print_r($this->params);
	    	list($result, $count) = $this->prepare();
	    	return [true, [], $this->getById($this->getLastInsertedId())];
    	} else {
    		$this->insertFields($comment);
	    	list($result, $count) = $this->prepare();

            //print_r($this->getById($this->getLastInsertedId()));
            return [true, [], $this->getById($this->getLastInsertedId())];
    	}

    }

    protected function insertFields(object $comment)
    {
    	// otherwise we continue. try to insert
    	$this->sql = "INSERT INTO {$this->table} (parent_id, name, comment, create_date) VALUES (?, ?, ?, ?)";
    	// push the values to params
    	$this->params[] = $comment->parent_id;
    	$this->params[] = $comment->name;
    	$this->params[] = $comment->comment;
    	$this->params[] = $comment->create_date;
    }

    public function validate(object $comment)
    {
    	// errors
    	$errors = null;

    	if (!$this->rules) {
    		$errors[] = 'Rules must be present before we can continue';
    	}

    	// loop through rules and check object
    	foreach($this->rules as $index => $rule) {
    		$rules = explode("|", $rule);
    		
    		foreach ($rules as $item) {
    			switch ($item) {
	    			case 'required':
	    				$error = !isset($comment->$index) ? "{$index} is required" : null;
	    			break;
	    			case 'integer':
	    				$error = is_int($comment->$index) ? null : "{$index} must be an integer";
	    			break;
	    			case 'string':
	    				$error = is_string($comment->$index) ? null : "{$index} must be a string";
    			}

    			if ($error) {
    				$errors[] = $error;
    			}
    		}
    	}
    	return $errors;
    }


}