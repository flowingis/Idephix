<?php

// libssh constant
define('LIBSSH2_ERROR_EAGAIN', -37);
define('SSH_MSG_USERAUTH_PK_OK', 60);
define('SSH_MSG_USERAUTH_REQUEST', 50);
define('SSH_MSG_USERAUTH_FAILURE', 51);
define('SSH_MSG_USERAUTH_SUCCESS', 52);
define('SSH_MSG_USERAUTH_BANNER', 53);

define('SSH2_AGENTC_REQUEST_IDENTITIES', 11);
define('SSH2_AGENT_IDENTITIES_ANSWER', 12);

define('SSH_AGENTC_REQUEST_RSA_IDENTITIES', 1);
define('SSH_AGENT_RSA_IDENTITIES_ANSWER', 2);
define('SSH_AGENT_FAILURE', 5);

define('libssh2_NB_state_idle', 1);
define('libssh2_NB_state_created', 2);
define('libssh2_NB_state_sent', 3);
define('libssh2_NB_state_sent1', 4);
define('libssh2_NB_state_sent2', 5);

/**
 * Description of AgentSshProxy
 *
 * @author kea
 */
class SshAgentProxy
{
  private $socket;
  private $keys;
  private $session;

  public function __construct($session) {
    $this->session = $session;
  }

  public function connect()
  {
    $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
    $address = $_SERVER['SSH_AUTH_SOCK'];

    if (socket_connect($socket, $address)) {
      echo "Connection successful on $address\n";
      $this->socket = $socket;
    }
    else {
      echo 'Unable to connect '.socket_strerror(socket_last_error());
    }
  }

  public function requestIdentities()
  {
      if (!$this->sendRequest(SSH2_AGENTC_REQUEST_IDENTITIES)) {
          echo 'Unable to request identities '.socket_strerror(socket_last_error());

          return false;
      }

      $bufferLenght = $this->readLength();
      $type = $this->readType();

      if ($type == SSH_AGENT_FAILURE) {
          return false;
      } elseif ($type != SSH_AGENT_RSA_IDENTITIES_ANSWER && $type != SSH2_AGENT_IDENTITIES_ANSWER) {
          throw new \Exception("Unknown response from agent: $type");
      }

      $buffer = socket_read($this->socket, $bufferLenght - 1);
      $keysCount = $this->binaryToLong($buffer);
      $buffer = substr($buffer, 4);

      for ($i = 0; $i < $keysCount; ++$i) {
          $this->keys[] = array(
                                'key' => $this->readPacketFromBuffer($buffer),
                                'comment' => $this->readPacketFromBuffer($buffer));
          var_dump($this->keys[count($this->keys) - 1]);
      }
  }

  private static function binaryToLong($binary) {
      return ((ord($binary[0]) * 256 + ord($binary[1])) * 256 + ord($binary[2])) * 256 + ord($binary[3]);
  }

  private static function intTo4Char($int) {
    return chr(($int / 256 / 256 / 256) % 256).
           chr(($int / 256 / 256) % 256).
           chr(($int / 256) % 256) .
           chr($int % 256);
  }

  private function sendRequest($type, $data = '') {
      $len = strlen($data) + 1;
      $buffer = self::intTo4Char($len).
                chr($type);

      return socket_write($this->socket, $buffer);
  }

  private function readLength() {
      $len = socket_read($this->socket, 4);

      return $this->binaryToLong($len);
  }

  private function readType() {
      return ord(socket_read($this->socket, 1));
  }

  private function readPacketFromBuffer(&$buffer) {
      $len = $this->binaryToLong($buffer);
      $packet = substr($buffer, 4, $len);
      $buffer = substr($buffer, $len + 4);

      return $packet;
  }

  public function login($username) {
    foreach ($this->keys as $key) {
        echo $this->userauthPublickey($this->session, $username, $key['key'], strlen($key['key']), $sign_callback);
    }
  }

  private function userauthPublickey(&$session,
                                    $username,
                                    $pubkeydata,
                                    $pubkeydata_len,
                                    $sign_callback)
  {
    $username_len = strlen($username);
    $reply_codes = array(SSH_MSG_USERAUTH_SUCCESS, SSH_MSG_USERAUTH_FAILURE, SSH_MSG_USERAUTH_PK_OK, 0);

    if ($session->userauth_pblc_state == libssh2_NB_state_idle) {

        /*
         * The call to self::binaryToLong later relies on pubkeydata having at
         * least 4 valid bytes containing the length of the method name.
         */
        if ($pubkeydata_len < 4) {
            return "Invalid public key, too short";
        }

        /*
         * As an optimisation, userauth_publickey_fromfile reuses a
         * previously allocated copy of the method name to avoid an extra
         * allocation/free.
         * For other uses, we allocate and populate it here.
         */
        if (!$session->userauth_pblc_method) {
            $session->userauth_pblc_method_len = self::binaryToLong(pubkeydata);

            if ($session->userauth_pblc_method_len > $pubkeydata_len)
                /* the method length simply cannot be longer than the entire
                   passed in data, so we use this to detect crazy input
                   data */
                return "Invalid public key";

            $session->userauth_pblc_method = substr($pubkeydata, 4, $session->userauth_pblc_method_len);
        }
        /*
         * The length of the method name read from plaintext prefix in the
         * file must match length embedded in the key.
         * TODO: The data should match too but we don't check that. Should we?
         */
        else if ($session->userauth_pblc_method_len !=
                 self::binaryToLong($pubkeydata))
            return "Invalid public key";

        /*
         * 45 = packet_type(1) + username_len(4) + servicename_len(4) +
         * service_name(14)"ssh-connection" + authmethod_len(4) +
         * authmethod(9)"publickey" + sig_included(1)'\0' + algmethod_len(4) +
         * publickey_len(4)
         */
        $session->userauth_pblc_packet_len =
            $username_len + $session->userauth_pblc_method_len + $pubkeydata_len +
            45;

        $s = SSH_MSG_USERAUTH_REQUEST;
        $s.= $username;
        $s.= "ssh-connection"."publickey"."\x0";

        $session->userauth_pblc_b = $s;

        $s.= $session->userauth_pblc_method;
        $s.= $pubkeydata;

        echo "Attempting publickey authentication";

        $session->userauth_pblc_state = libssh2_NB_state_created;
    }

    if ($session->userauth_pblc_state == libssh2_NB_state_created) {
        $rc = _libssh2_transport_send(session, $session->userauth_pblc_packet,
                                     $session->userauth_pblc_packet_len,
                                     NULL, 0);
        if ($rc == LIBSSH2_ERROR_EAGAIN)
            return _libssh2_error(session, LIBSSH2_ERROR_EAGAIN, "Would block");
        elseif ($rc) {
            $session->userauth_pblc_packet = NULL;
            $session->userauth_pblc_method = NULL;
            $session->userauth_pblc_state = libssh2_NB_state_idle;
            return "Unable to send userauth-publickey request";
        }

        $session->userauth_pblc_state = libssh2_NB_state_sent;
    }

    if ($session->userauth_pblc_state == libssh2_NB_state_sent) {
        $rc = _libssh2_packet_requirev($session, $reply_codes,
                                      $session->userauth_pblc_data,
                                      $session->userauth_pblc_data_len, 0,
                                      NULL, 0,
                                      $session->
                                      $userauth_pblc_packet_requirev_state);
        if ($rc == LIBSSH2_ERROR_EAGAIN) {
            return "Would block";
        }
        elseif ($rc) {
            $session->userauth_pblc_packet = NULL;
            $session->userauth_pblc_method = NULL;
            $session->userauth_pblc_state = libssh2_NB_state_idle;
            return "Waiting for USERAUTH response";
        }

        if ($session->userauth_pblc_data[0] == SSH_MSG_USERAUTH_SUCCESS) {
            echo "Pubkey authentication prematurely successful";
            /*
             * God help any SSH server that allows an UNVERIFIED
             * public key to validate the user
             */
            $session->userauth_pblc_data = NULL;
            $session->userauth_pblc_packet = NULL;
            $session->userauth_pblc_method = NULL;
            $session->state |= LIBSSH2_STATE_AUTHENTICATED;
            $session->userauth_pblc_state = libssh2_NB_state_idle;
            return 0;
        }

        if ($session->userauth_pblc_data[0] == SSH_MSG_USERAUTH_FAILURE) {
            /* This public key is not allowed for this user on this server */
            $session->userauth_pblc_data = NULL;
            $session->userauth_pblc_packet = NULL;
            $session->userauth_pblc_method = NULL;
            $session->userauth_pblc_state = libssh2_NB_state_idle;
            return "Username/PublicKey combination invalid";
        }

        /* Semi-Success! */
        $session->userauth_pblc_data = NULL;

        $session->userauth_pblc_b = chr(1);
        $session->userauth_pblc_state = libssh2_NB_state_sent1;
    }

    if ($session->userauth_pblc_state == libssh2_NB_state_sent1) {
        $s = $session->session_id;
        $s.= $session->userauth_pblc_packet;

        // @todo da verificare
        $rc = sign_callback($session, $sig, $sig_len, $buf, $s - $buf, $abstract);

        if ($rc == LIBSSH2_ERROR_EAGAIN) {
            return "Would block";
        } else if ($rc) {
            $session->userauth_pblc_method = NULL;
            $session->userauth_pblc_packet = NULL;
            $session->userauth_pblc_state = libssh2_NB_state_idle;
            return "Callback returned error";
        }

        /*
         * If this function was restarted, pubkeydata_len might still be 0
         * which will cause an unnecessary but harmless realloc here.
         */
        if ($sig_len > $pubkeydata_len) {
            /* NON HO CAPITO BENE... */
//            $session->userauth_pblc_packet = newpacket;
        }

        $session->userauth_pblc_b = NULL;

        $s.= self::intTo4Char(4 + $session->userauth_pblc_method_len + 4 + $sig_len);
        $s.= $session->userauth_pblc_method;

        $session->userauth_pblc_method = NULL;

        $s.= $sig;

        echo "Attempting publickey authentication -- phase 2";

        $session->userauth_pblc_s = s;
        $session->userauth_pblc_state = libssh2_NB_state_sent2;
    }

    if ($session->userauth_pblc_state == libssh2_NB_state_sent2) {
        $rc = _libssh2_transport_send($session, $session->userauth_pblc_packet,
                                     $session->userauth_pblc_s -
                                     $session->userauth_pblc_packet,
                                     NULL, 0);
        if ($rc == LIBSSH2_ERROR_EAGAIN) {
            return "Would block";
        } elseif ($rc) {
            $session->userauth_pblc_packet = NULL;
            $session->userauth_pblc_state = libssh2_NB_state_idle;
            return "Unable to send userauth-publickey request";
        }
        $session->userauth_pblc_packet = NULL;
        $session->userauth_pblc_state = libssh2_NB_state_sent3;
    }

    /* PK_OK is no longer valid */
    $reply_codes[2] = 0;

    $rc = _libssh2_packet_requirev($session, $reply_codes,
                                  $session->userauth_pblc_data,
                                  $session->userauth_pblc_data_len, 0, NULL, 0,
                                  $session->userauth_pblc_packet_requirev_state);
    if (rc == LIBSSH2_ERROR_EAGAIN) {
        return _libssh2_error(session, LIBSSH2_ERROR_EAGAIN,
                              "Would block requesting userauth list");
    } else if (rc) {
        $session->userauth_pblc_state = libssh2_NB_state_idle;
        return _libssh2_error(session, LIBSSH2_ERROR_PUBLICKEY_UNVERIFIED,
                              "Waiting for publickey USERAUTH response");
    }

    if ($session->userauth_pblc_data[0] == SSH_MSG_USERAUTH_SUCCESS) {
        echo "Publickey authentication successful";
        /* We are us and we've proved it. */
        $session->userauth_pblc_data = NULL;
        $session->state |= LIBSSH2_STATE_AUTHENTICATED;
        $session->userauth_pblc_state = libssh2_NB_state_idle;
        return 0;
    }

    /* This public key is not allowed for this user on this server */
    $session->userauth_pblc_data = NULL;
    $session->userauth_pblc_state = libssh2_NB_state_idle;
    return "Invalid signature for supplied public key, or bad username/public key combination";
  }

}

function disconnect($reason, $message, $language) { $session = NULL; }
$session = ssh2_connect('localhost', 22, NULL, array('disconnect', 'disconnect'));

$s = new SshAgentProxy($session);
$s->connect();
$s->requestIdentities();
$s->login('kea');
