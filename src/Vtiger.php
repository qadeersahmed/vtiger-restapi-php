<?php

namespace VTigerClient;

class Vtiger
{
    public $serveraddress;
    public $userName;
    public $userAccessKey;
    public $sessionName;

    public function __construct($serveraddress, $userName, $userAccessKey)
    {
        $this->serveraddress = $serveraddress . "/webservice.php";
        $this->userName = $userName;
        $this->userAccessKey = $userAccessKey;
        $this->login();
    }

    private function getToken(): string
    {
        $data = [
            'operation' => 'getchallenge',
            'username'  => $this->userName
        ];

        $token_data = $this->sendHttpRequest($data, 'GET');
        return $token_data->result->token;
    }

    private function login(): void
    {
        $token = $this->getToken();
        $data = array(
            'operation' => 'login',
            'username'  => $this->userName,
            'accessKey' => md5($token . $this->userAccessKey),
        );
        $result = $this->sendHttpRequest($data, 'POST');
        $this->sessionName = $result->result->sessionName;
    }

    public function create(array $params, string $module): object
    {
        $element = json_encode($params);
        $data = array(
            'operation'   => 'create',
            'sessionName' => $this->sessionName,
            'element'     => $element,
            'elementType' => $module
        );
        return $this->sendHttpRequest($data, 'POST');
    }

    public function update(array $params): object
    {
        $element = json_encode($params);
        $data = array(
            'operation'   => 'update',
            'sessionName' => $this->sessionName,
            'element'     => $element
        );
        return $this->sendHttpRequest($data, 'POST');
    }

    public function retrieve(string $id): object
    {
        $data = array(
            'operation'     => 'retrieve',
            'sessionName'   => $this->sessionName,
            'id'            => $id
        );
        return $this->sendHttpRequest($data, 'GET');
    }

    public function revise(array $params): object
    {
        $element = json_encode($params);

        $data = array(
            'operation'     => 'revise',
            'sessionName'   => $this->sessionName,
            'element'       => $element
        );
        return $this->sendHttpRequest($data, 'POST');
    }

    public function describe(string $module): object
    {
        $data = array(
            'operation'     => 'describe',
            'sessionName'   => $this->sessionName,
            'elementType'   => $module
        );
        return $this->sendHttpRequest($data, 'GET');
    }

    public function listTypes(): object
    {
        $data = array(
            'operation'     => 'listtypes',
            'sessionName'   => $this->sessionName
        );
        return $this->sendHttpRequest($data, 'GET');
    }

    public function retrieveRelated(string $id, string $targetLabel, string $targetModule): object
    {
        $data = array(
            'operation'     => 'retrieve_related',
            'sessionName'   => $this->sessionName,
            'id'            => $id,
            'relatedLabel'  => $targetLabel,
            'relatedType'   => $targetModule,
        );
        return $this->sendHttpRequest($data, 'GET');
    }

    public function query(string $module, array $params, array $select = []): object
    {
        $query = $this->getQueryString($module, $params, $select);
        $data = array(
            'operation'     => 'query',
            'sessionName'   => $this->sessionName,
            'query'         => $query
        );
        return $this->sendHttpRequest($data, 'GET');
    }

    private function getQueryString(string $moduleName, array $params, array $select = []): string
    {
        $criteria = array();
        $select = (empty($select)) ? '*' : implode(',', $select);
        $query = sprintf("SELECT %s FROM $moduleName", $select);

        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $criteria[] = "{$param} = '{$value}'";
            }

            $query .= sprintf(' WHERE %s ;', implode(" AND ", $criteria));
        }
        return $query;
    }

    public function sendHttpRequest(array $data, string $method): object
    {
        $client = new \GuzzleHttp\Client();

        switch ($method) {
            case 'GET':
                $response = $client->request('GET', $this->serveraddress, ['query' => $data])->getBody();
                break;

            case 'POST':
                $response = $client->request('POST', $this->serveraddress, ['form_params' => $data])->getBody();
                break;
        }
        $response = json_decode($response);
        if (!$response->success) {
            throw new \Exception($response->error->code . ": " . $response->error->message);
        }
        return $response;
    }
}
