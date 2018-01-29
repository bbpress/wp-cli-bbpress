Feature: Manage bbPress subscriptions

  Scenario: Favorite CRUD commands.
    Given a bbPress install

    When I run `wp user create testuser1 testuser1@example.com --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {MEMBER_ID}

    When I run `wp bbp topic create --title="Topic" --porcelain`
    Then STDOUT should be a number
    And save STDOUT as {TOPIC_ID}

    When I run `wp bbp favorite add --user-id={MEMBER_ID} --topic-id={TOPIC_ID}`
    Then STDOUT should contain:
      """
      Success: Favorite successfully added.
      """

    When I run `wp bbp favorite list_users {TOPIC_ID} --format=ids`
    Then STDOUT should contain:
      """
      {MEMBER_ID}
      """

    When I run `wp bbp favorite remove --user-id={MEMBER_ID} --topic-id={TOPIC_ID} --yes`
    Then STDOUT should contain:
      """
      Success: Favorite successfully removed.
      """

    When I try `wp bbp favorite remove --user-id={MEMBER_ID} --topic-id={TOPIC_ID} --yes`
    Then the return code should be 1
