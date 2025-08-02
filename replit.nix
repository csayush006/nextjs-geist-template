{ pkgs }: {
  deps = [
    pkgs.php
    pkgs.nodejs
    pkgs.nodePackages.npm
  ];
}
