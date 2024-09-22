<?php
/**
 * Create Contact to MailMint
 *
 * @since 8.4.10
 */
class WPVR_Create_Contact {

    protected $webHookUrl = [WPVR_WEBHOOK_URL];

    /**
     * Email
     *
     * @var string
     * @since 8.4.10
     */
    protected $email = '';

    /**
     * Name
     *
     * @var string
     * @since 8.4.10
     */
    protected $name = '';


    /**
     * Industry
     *
     * @var string
     * @since 8.4.10
     */
    protected $industry = '';


    /**
     * Constructor
     *
     * @param string $email
     * @param string $name
     * @since 8.4.10
     */
    public function __construct( $email, $name, $industry ){
        $this->email = $email;
        $this->name = $name;
        $this->industry = $industry;
    }


    /**
     * Create contact to MailMint via webhook
     *
     * @return array
     * @since 8.4.10
     */
    public function create_contact_via_webhook(){
        if( !$this->email ){
            return [
                'suceess' => false,
            ];
        }

        $response = [
            'suceess' => true,
        ];

        $industry_name = !empty( get_option('wpvr_industry_name') ) ? get_option('wpvr_industry_name') : '';
        $json_body_data = json_encode([
            'email'         => $this->email,
            'first_name'    => $this->name,
            'meta_fields'   => [
                'industry' => $this->industry,
            ],
        ]);

        try{
            if( !empty($this->webHookUrl ) ){
                foreach( $this->webHookUrl as $url ){
                    $response = wp_remote_request($url, [
                        'method'    => 'POST',
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                        'body' => $json_body_data
                    ]);
                }
            }
        }catch(\Exception $e){
            error_log('Error sending contact data to MailMint');
            $response = [
                'suceess' => false,
            ];
        }

        return $response;
    }
}
?>