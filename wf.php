<meta charset="utf-8">
<?php

/**
 * API @todo
 * - login
 * - get initialMostRecentOperationTransactionId, clientId for next operations
 * - get tree (complete ? partials ?)
 * - move item
 * - complete item
 * - delete item
 * - edit item name
 * - add note
 * - get settings (username, itemsCreatedInCurrentMonth, monthlyItemQuota, dateJoinedTimestampInSeconds)
 * - get currently expanded projects (serverExpandedProjectsList) (and toggle expand ? how ?)
 */

$session_id = login('workflowy1@yopmail.com', 'workflowy1');

session_start();

$most_recent_operation_transaction_id = !empty($_SESSION['most_recent_operation_transaction_id']) ? $_SESSION['most_recent_operation_transaction_id'] : '370522486';

request('get_initialization_data');

request('get_settings');

$generated_test_id = generate_id();
request('push_and_poll', array(
    'client_id' => '2014-12-05 08:13:15.458597',
    'client_version' => 14,
    'push_poll_id' => 'MSQBGpdw',
    'push_poll_data' => json_encode(array(
        (object)array(
            'most_recent_operation_transaction_id' => $most_recent_operation_transaction_id,
            'operations' => array(
                (object)array(
                    'type' => 'edit',
                    'data' => (object)array(
                        'projectid' => '43c2893f-8cc3-9127-f806-ccbe8b25351b',
                        'name' => 'test avec sub'
                    )
                ),
                (object)array(
                    'type' => 'move',
                    'data' => (object)array(
                        'projectid' => '43c2893f-8cc3-9127-f806-ccbe8b25351b',
                        'parentid' => 'None',
                        'priority' => 1
                    )
                ),
                (object)array(
                    'type' => 'edit',
                    'data' => (object)array(
                        'projectid' => '43c2893f-8cc3-9127-f806-ccbe8b25351b',
                        'description' => 'Description: ' . date('d-m-Y H:i:s')
                    )
                ),
                (object)array(
                    'type' => 'create',
                    'data' => (object)array(
                        'projectid' => $generated_test_id,
                        'parentid' => 'None',
                        'priority' => 6
                    )
                ),
                (object)array(
                    'type' => 'edit',
                    'data' => (object)array(
                        'projectid' => $generated_test_id,
                        'name' => 'Le nom du nouvel item'
                    )
                )
            )
        ))
    )
));

function generate_id()
{
    //return((1+Math.random())*65536|0).toString(16).substring(1)
    // k()+k()+"-"+k()+"-"+k()+"-"+k()+"-"+k()+k()+k()
    return preg_replace_callback('#r#', function()
    {
        return substr(base_convert((1 + random()) * 65536 | 0, 10, 16), 1);
    }, 'rr-r-r-r-rrr');
}

function random()
{
    return (float)rand()/(float)getrandmax();
}

function request($method, $params = false)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://workflowy.com/' . $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    if ($params !== false)
    {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: sessionid=78d9fe4c88f69b58d7c0bb7ac24953b9'));
    $data = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (!empty($data['results'][0]['new_most_recent_operation_transaction_id']))
    {
        $_SESSION['most_recent_operation_transaction_id'] = $data['results'][0]['new_most_recent_operation_transaction_id'];
    }
    if (!empty($data['results'][0]['server_run_operation_transaction_json']))
    {
        $data['results'][0]['server_run_operation_transaction'] = json_decode($data['results'][0]['server_run_operation_transaction_json'], true);
    }

    echo '<h2>' . $method . '</h2>';
    printr($params);
    printr($data);
    return $data;
}

function login($username, $password)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://workflowy.com/accounts/login/');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('username' => $username, 'password' => $password, 'next' => ''));
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $raw_data = curl_exec($ch);
    curl_close($ch);

    preg_match('#^Set-Cookie:\s*sessionid=([^;]*)#mi', $raw_data, $session_id_match);
    $session_id = !empty($session_id_match[1]) ? $session_id_match[1] : false;

    return preg_match('#^Location:#mi', $raw_data) ? $session_id : false;
}

function printr($thing)
{
    echo '<pre>';
    print_r($thing);
    echo '</pre>';
}
