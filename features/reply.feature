Feature: Manage bbPress Replies

  Background:
    Given a WP install

  Scenario: Create a reply
    When I run `wp bbp reply create --title="Title" --content="Content" --topic-id=456 --forum-id=456`
    Then STDOUT should contain:
      """
      Success: Reply 81 created: http://site.com/forums/reply/reply-test/
      """

  Scenario: Delete a reply
    When I run `wp bbp reply delete 520`
    Then STDOUT should contain:
      """
      Success: Reply 520 successfully deleted.
      """

  Scenario: Trash a reply
    When I run `wp bbp reply trash 789`
    Then STDOUT should contain:
      """
      Success: Reply 789 successfully trashed.
      """

  Scenario: Untrash a reply
    When I run `wp bbp reply untrash 784`
    Then STDOUT should contain:
      """
      Success: Reply 784 successfully untrashed.
      """

  Scenario: Spam a reply
    When I run `wp bbp reply spam 498`
    Then STDOUT should contain:
      """
      Success: Reply 498 successfully spammed.
      """

  Scenario: Ham a reply
    When I run `wp bbp reply ham 368`
    Then STDOUT should contain:
      """
      Success: Reply 368 successfully hammed.
      """

  Scenario: Approve a reply
    When I run `wp bbp reply approve 1234`
    Then STDOUT should contain:
      """
      Success: Reply 1234 successfully approved.
      """

  Scenario: Unapprove a reply
    When I run `wp bbp reply unapprove 6654`
    Then STDOUT should contain:
      """
      Success: Reply 6654 successfully unapproved.
      """

  Scenario: Get permalink of a reply
    When I run `wp bbp reply permalink 500`
    Then STDOUT should contain:
      """
      Success: Reply Permalink: http://site.com/forums/reply/reply-slug/
      """

    When I run `wp bbp reply url 156`
    Then STDOUT should contain:
      """
      Success: Reply Permalink: http://site.com/forums/reply/another-reply-slug/
      """
