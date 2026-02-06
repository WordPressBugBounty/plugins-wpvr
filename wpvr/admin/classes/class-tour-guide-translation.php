<?php

Class Tour_Guide_Translation
{
    public function get_translatable_string()
    {
        return array(
            'next_button_text' => __('Next', 'wpvr'),
            'previous_button_text' => __('Previous', 'wpvr'),
            'done_text' => __('Done', 'wpvr'),
            'end_tour' => __('Skip Tour', 'wpvr'),
        );
    }
}