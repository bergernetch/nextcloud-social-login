<?php

namespace OCA\SocialLogin\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Data;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\User;

class CustomOAuth2 extends OAuth2
{
    /**
     * @return User\Profile
     * @throws UnexpectedApiResponseException
     * @throws \Hybridauth\Exception\HttpClientFailureException
     * @throws \Hybridauth\Exception\HttpRequestFailedException
     * @throws \Hybridauth\Exception\InvalidAccessTokenException
     */
    public function getUserProfile()
    {
        $profileUrl = $this->config->get('endpoints')['profile_url'];
        $response = $this->apiRequest($profileUrl);

        // PlanningCenter Field Mapping
        if (isset($response->data->attributes->first_name)) {
            $response->firstName = $response->data->attributes->first_name; 
        }
        if (isset($response->data->attributes->last_name)) {
            $response->lastName = $response->data->attributes->last_name; 
        }
        if (isset($response->data->attributes->name)) {
            $response->displayName = $response->data->attributes->name;    
        }
        if (isset($response->data->attributes->avatar)) {    
            $response->photoURL = $response->data->attributes->avatar; 
        }

        if (isset($response->data->id)) {
            $response->identifier = $response->data->id . "_" . $response->lastName . "_" . $response->firstName;
        }

        $data = new Data\Collection($response);

        if (!$data->exists('identifier')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response, missing identifier (getUserProfile)');
        }

        $userProfile = new User\Profile();
        foreach ($data->toArray() as $key => $value) {
            if ($key !== 'data' && property_exists($userProfile, $key)) {
                $userProfile->$key = $value;
            }
        }

        // get Groups from PlanningCenter, tab "NextCloud Groups", needs personid
        $userProfile->data['groups'] = $this->getPlanningCenterGroups($response->data->id);

        return $userProfile;
    }

    protected function getPlanningCenterGroups($personID){
        // field id can be taken from the pco website where the nextcloud groups are defined.
        // alternatively from the api explorer/postman
        $groupFieldDefinitionId = $this->config->get('profile_fields');
        $groupsURL = "https://api.planningcenteronline.com/people/v2/people/".$personID."/field_data?where[field_definition_id]=".$groupFieldDefinitionId;
        $response = $this->apiRequest($groupsURL);

        $planningcentergroups = [];

        if ($response === null) {
            throw new UnexpectedApiResponseException('Planning Center API returned an unexpected response in getPlanningCenterGroups().');
        }

        // loop over array of fields
        foreach ($response->data as $notusedkey => $field) {
            // loop over the items
            foreach ($field as $key => $value) {
                if($key == "attributes"){
                    // loop over the items in attributes
                    foreach ($value as $item => $group) {
                        // extract the group values
                        if($item == "value"){
                            $planningcentergroups[] = $group;
                        }
                    }
                }
            }
        }

        return $planningcentergroups;
    }

}
