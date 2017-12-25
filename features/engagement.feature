Feature: Manage bbPress engagements

  Scenario: Engagement CRUD commands.
    Given a bbPress install

    When I run `wp user create testuser1 testuser1@example.com --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {MEMBER_ID}

    When I run `wp bbp topic create --title="Topic" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {TOPIC_ID}

    When I run `wp bbp engagement add --user-id={MEMBER_ID} --topic-id={TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Engagement successfully added.
      """

    When I run `wp bbp engagement list_users --topic-id={TOPIC_ID} --format=ids`
    Then STDOUT should contain:
      """
      {MEMBER_ID}
      """

    When I run `wp bbp engagement list {MEMBER_ID} --format=count`
    Then STDOUT should contain:
      """
      1
      """

    When I run `wp bbp engagement list {MEMBER_ID} --format=ids`
    Then STDOUT should contain:
      """
      {TOPIC_ID}
      """

    When I run `wp bbp engagement recalculate {TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Engagements successfully recalculated.
      """

    When I run `wp bbp engagement remove --user-id={MEMBER_ID} --topic-id={TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Engagement successfully removed.
      """

    When I run `wp bbp engagement remove --user-id={MEMBER_ID} --topic-id={TOPIC_ID} --yes`
    Then the return code should be 1
