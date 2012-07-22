<?php
/*
 Plugin Name: Interesting Followers
 Description: New discerning followers.
 */

/**
 *
 * ThinkUp/webapp/plugins/insightsgenerator/insights/interestingfollowers.php
 *
 * Copyright (c) 2012 Gina Trapani
 *
 * LICENSE:
 *
 * This file is part of ThinkUp (http://thinkupapp.com).
 *
 * ThinkUp is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any
 * later version.
 *
 * ThinkUp is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with ThinkUp.  If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * @license http://www.gnu.org/licenses/gpl.html
 * @copyright 2012 Gina Trapani
 * @author Gina Trapani <ginatrapani [at] gmail [dot] com>
 */

class InterestingFollowersInsight extends InsightPluginParent implements InsightPlugin {

    public function generateInsight(Instance $instance, $last_week_of_posts, $number_days) {
        parent::generateInsight($instance, $last_week_of_posts, $number_days);
        $this->logger->logInfo("Begin generating insight", __METHOD__.','.__LINE__);
        $insight_dao = DAOFactory::getDAO('InsightDAO');

        // Least likely followers insights
        $follow_dao = DAOFactory::getDAO('FollowDAO');
        $days_ago = 0;
        while ($days_ago < $number_days) {
            //For each of the past 7 days (remove this later & just do day by day?)
            //get least likely followers for that day
            $least_likely_followers = $follow_dao->getLeastLikelyFollowersByDay($instance->network_user_id,
            $instance->network, $days_ago, 3);
            if (sizeof($least_likely_followers) > 0 ) { //if not null, store insight
                //If followers have more followers than half of what the instance has, jack up emphasis
                $emphasis = Insight::EMPHASIS_LOW;
                foreach ($least_likely_followers as $least_likely_follower) {
                    if ($least_likely_follower->follower_count > ($this->user->follower_count/2)) {
                        $emphasis = Insight::EMPHASIS_HIGH;
                    }
                }

                $insight_date = new DateTime();
                //Not PHP 5.2 compatible
                //$insight_date->sub(new DateInterval('P'.$days_ago.'D'));
                $insight_date->modify('-'.$days_ago.' day');
                $insight_date = $insight_date->format('Y-m-d');
                if (sizeof($least_likely_followers) > 1) {
                    $insight_dao->insertInsight('least_likely_followers', $instance->id, $insight_date,
                    "Good people:", sizeof($least_likely_followers)." interesting users followed you.",
                    $emphasis, serialize($least_likely_followers));
                } else {
                    $insight_dao->insertInsight('least_likely_followers', $instance->id, $insight_date,
                    "Hey!", "An interesting user followed you.",
                    $emphasis, serialize($least_likely_followers));
                }
            }
            $days_ago++;
        }
        $this->logger->logInfo("Done generating insight", __METHOD__.','.__LINE__);
    }
}

$insights_plugin_registrar = PluginRegistrarInsights::getInstance();
$insights_plugin_registrar->registerInsightPlugin('InterestingFollowersInsight');
