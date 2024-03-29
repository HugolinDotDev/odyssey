<?php
/**
 * User entity
 * 
 * @author Hugolin Mariette, Abdelnor Ait Ali, Yuxuan Sun
 */

namespace App\Entities;

use Core\Entity\BaseEntity;

class UserEntity extends BaseEntity {

    public function login(string $mail_address, string $password) {
        $request = 'SELECT *
            FROM utilisateur
            WHERE mail = :mail_address';
        $stmt = $this->db_connection::getInstance()->prepare($request);
        $stmt->bindValue(':mail_address', $mail_address);
        $stmt->execute();
        $res = $stmt->fetch();
        if (!$res) return null;
        if (!password_verify($password, $res['mot_de_passe'])) return null;
        return $res;
    }

    public function register(string $firstname, string $lastname, string $mail_address, string $password) {
        $request = 'INSERT INTO utilisateur(prenom, nom, mail, mot_de_passe)
            VALUES (:firstname, :lastname, :mail_address, :password)';
        $stmt = $this->db_connection::getInstance()->prepare($request);
        $stmt->bindValue(':firstname', $firstname);
        $stmt->bindValue(':lastname', $lastname);
        $stmt->bindValue(':mail_address', $mail_address);
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindValue(':password', $hashed_password);
        $res = $stmt->execute();
        return $res;
    }

    public function exists(string $mail_address) {
        $request = 'SELECT id_utilisateur FROM utilisateur
            WHERE mail = :mail_address';
        $stmt = $this->db_connection::getInstance()->prepare($request);
        $stmt->bindValue(':mail_address', $mail_address);
        $stmt->execute();
        $res = $stmt->fetch();
        if (!$res) return null;
        return $res;
    }

}