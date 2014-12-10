<meta charset="utf-8">
<?php

/**
 * API @todo
 * - login
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
printr($session_id);

$init_data = request('get_initialization_data');
$most_recent_operation_transaction_id = $init_data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId'];
$client_id = $init_data['projectTreeData']['clientId'];
printr($most_recent_operation_transaction_id);
printr($client_id);

$settings = request('get_settings');
printr($settings);

$generated_test_id = generate_id();
$request = request('push_and_poll', array(
    'client_id'      => $client_id,
    'client_version' => 14,
    'push_poll_id'   => 'MSQBGpdw',
    'push_poll_data' => json_encode(array(
            (object)array(
                'most_recent_operation_transaction_id' => $most_recent_operation_transaction_id,
                'operations'                           => array(
                    (object)array(
                        'type' => 'edit',
                        'data' => (object)array(
                            'projectid' => '43c2893f-8cc3-9127-f806-ccbe8b25351b',
                            'name'      => 'test avec sub'
                        )
                    ),
                    (object)array(
                        'type' => 'move',
                        'data' => (object)array(
                            'projectid' => '43c2893f-8cc3-9127-f806-ccbe8b25351b',
                            'parentid'  => 'None',
                            'priority'  => 1
                        )
                    ),
                    (object)array(
                        'type' => 'edit',
                        'data' => (object)array(
                            'projectid'   => '43c2893f-8cc3-9127-f806-ccbe8b25351b',
                            'description' => 'Description: ' . date('d-m-Y H:i:s')
                        )
                    ),
                    (object)array(
                        'type' => 'create',
                        'data' => (object)array(
                            'projectid' => $generated_test_id,
                            'parentid'  => 'None',
                            'priority'  => 6
                        )
                    ),
                    (object)array(
                        'type' => 'edit',
                        'data' => (object)array(
                            'projectid' => $generated_test_id,
                            'name'      => 'Le nom du nouvel item'
                        )
                    )
                )
            )
        )
    )
));
printr($request);

function generate_id()
{
    //return((1+Math.random())*65536|0).toString(16).substring(1)
    // k()+k()+"-"+k()+"-"+k()+"-"+k()+"-"+k()+k()+k()
    return preg_replace_callback('#r#', function ()
    {
        return substr(base_convert((1 + ((float)rand() / (float)getrandmax())) * 65536 | 0, 10, 16), 1);
    }, 'rr-r-r-r-rrr');
}

function request($method, $params = array())
{
    $raw_data = do_curl('https://workflowy.com/' . $method, $params, array(CURLOPT_HTTPHEADER => array('Cookie: sessionid=' . $GLOBALS['session_id'])));
    $data     = json_decode($raw_data, true);
    if (!empty($data['results'][0]['new_most_recent_operation_transaction_id']))
    {
        $GLOBALS['most_recent_operation_transaction_id'] = $data['results'][0]['new_most_recent_operation_transaction_id'];
    }
    return $data;
}

function login($username, $password)
{
    $raw_data = do_curl('https://workflowy.com/accounts/login/', array('username' => $username, 'password' => $password, 'next' => ''), array(CURLOPT_HEADER => true));
    preg_match('#^Set-Cookie:\s*sessionid=([^;]*)#mi', $raw_data, $session_id_match);
    $session_id = !empty($session_id_match[1]) ? $session_id_match[1] : false;

    return preg_match('#^Location:#mi', $raw_data) ? $session_id : false;
}

function do_curl($url, $post_fields, $curl_options)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    foreach ($curl_options as $option => $value)
    {
        curl_setopt($ch, $option, $value);
    }
    $raw_data = curl_exec($ch);
    curl_close($ch);
    return $raw_data;
}

function printr($thing)
{
    echo '<pre>';
    print_r($thing);
    echo '</pre>';
}
