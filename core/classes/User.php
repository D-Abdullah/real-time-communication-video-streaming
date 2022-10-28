<?php
class User
{
    public $db, $sessionId;
    public function __construct()
    {
        $db = new DB;
        $this->db = $db->connect();
        $this->sessionId = $this->getSessionId();
    }
    public function getSessionId()
    {
        return session_id();
    }
    public function userExist($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `username` = :username");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if (!empty($user)) {
            return $user;
        } else {
            return false;
        }
    }
    public function getUser($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `id` = :userId");
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if (!empty($user)) {
            return $user;
        } else {
            return false;
        }
    }
    public function getAllUsers($id)
    {
        $stmt = $this->db->prepare("SELECT `name`, `image` FROM `users` WHERE `id` != :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($user)) {
            return $user;
        } else {
            return false;
        }
    }
    public function getUserByName($name)
    {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `name` = :name");
        $stmt->bindParam(":name", $name, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if (!empty($user)) {
            return $user;
        } else {
            return false;
        }
    }
    public function updateSession()
    {
        $stmt = $this->db->prepare("UPDATE `users` SET `session_id` = :session_id WHERE id = :id");
        $stmt->bindParam(":session_id", $this->sessionId, PDO::PARAM_STR);
        $stmt->bindParam(":id", $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
    }
    public function getUserBySession($session_id)
    {
        $stmt = $this->db->prepare("SELECT * FROM `users` WHERE `session_id` = :session_id");
        $stmt->bindParam(":session_id", $session_id, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        if (!empty($user)) {
            return $user;
        } else {
            return false;
        }
    }
}
