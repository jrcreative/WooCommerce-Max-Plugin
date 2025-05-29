{ pkgs }: {
	deps = [
    pkgs.apacheHttpd
    pkgs.mariadb
    pkgs.php82
    pkgs.envsubst
	];
}
