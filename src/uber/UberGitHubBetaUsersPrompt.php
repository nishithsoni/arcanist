<?php

// UberGitHubBetaUsersPrompt
final class UberGitHubBetaUsersPrompt extends Phobject {
  private $url = self::URL;
  const URL = 'https://gollo.uberinternal.com/r?request=get-group-members&group=github-beta-users-prompt';

  const TIMEOUT_SECONDS = 10;

  public function __construct() {}

  public function getGitHubBetaUsersPromptGroup(): array {
    try {
      $usso = new UberUSSO();
      $hostname = parse_url(self::URL, PHP_URL_HOST);
      $token = $usso->maybeUseUSSOToken($hostname);
      if (!$token) {
        $token = $usso->getUSSOToken($hostname);
      }

      $future = id(new HTTPSFuture($this->url))
        ->setFollowLocation(false)
        ->setTimeout(self::TIMEOUT_SECONDS)
        ->setMethod('GET')
        ->addHeader('Authorization', "Bearer {$token}");

      list($body, $headers) = $future->resolvex();

      if (empty($body)) {
        return [];
      }

      $resp = phutil_json_decode($body);
      return $resp['Members'] ?? [];
    } catch (Exception $e) {
      return [];
    }
  }


  public function isCurrentUserInGitHubBetaUsersPromptGroup() {
    $githubBetaUsers = $this->getGitHubBetaUsersPromptGroup();
    $user = getenv('USER');
    if ($user === false) {
      return false;
    }
    $userEmail = $user . '@uber.com';
    return in_array($userEmail, $githubBetaUsers);
  }

}
