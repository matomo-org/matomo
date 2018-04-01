#!/usr/bin/env perl

use strict;
use warnings;
use autodie;
use utf8;

use Carp qw( croak );
use Cwd qw( abs_path );
use File::Basename qw( dirname );
use File::Slurper qw( read_binary write_binary );
use Cpanel::JSON::XS qw( decode_json );
use Math::Int128 qw( uint128 );
use MaxMind::DB::Writer::Serializer 0.100004;
use MaxMind::DB::Writer::Tree 0.100004;
use MaxMind::DB::Writer::Util qw( key_for_data );
use Net::Works::Network;
use Test::MaxMind::DB::Common::Util qw( standard_test_metadata );

my $Dir = dirname( abs_path($0) );

sub main {
    _write_geoip2_db( @{$_}[ 0, 1 ] )
        for (
        ['GeoIP2-City'],
        ['GeoIP2-Country'],
        );}

sub _universal_map_key_type_callback {
    my $map = {

        # languages
        de      => 'utf8_string',
        en      => 'utf8_string',
        es      => 'utf8_string',
        fr      => 'utf8_string',
        ja      => 'utf8_string',
        'pt-BR' => 'utf8_string',
        ru      => 'utf8_string',
        'zh-CN' => 'utf8_string',

        # production
        accuracy_radius                => 'uint16',
        autonomous_system_number       => 'uint32',
        autonomous_system_organization => 'utf8_string',
        average_income                 => 'uint32',
        city                           => 'map',
        code                           => 'utf8_string',
        confidence                     => 'uint16',
        connection_type                => 'utf8_string',
        continent                      => 'map',
        country                        => 'map',
        domain                         => 'utf8_string',
        geoname_id                     => 'uint32',
        ipv4_24                        => 'uint32',
        ipv4_32                        => 'uint32',
        ipv6_32                        => 'uint32',
        ipv6_48                        => 'uint32',
        ipv6_64                        => 'uint32',
        is_anonymous                   => 'boolean',
        is_anonymous_proxy             => 'boolean',
        is_anonymous_vpn               => 'boolean',
        is_hosting_provider            => 'boolean',
        is_in_european_union           => 'boolean',
        is_legitimate_proxy            => 'boolean',
        is_public_proxy                => 'boolean',
        is_satellite_provider          => 'boolean',
        is_tor_exit_node               => 'boolean',
        iso_code                       => 'utf8_string',
        isp                            => 'utf8_string',
        latitude                       => 'double',
        location                       => 'map',
        longitude                      => 'double',
        metro_code                     => 'uint16',
        names                          => 'map',
        organization                   => 'utf8_string',
        population_density             => 'uint32',
        postal                         => 'map',
        registered_country             => 'map',
        represented_country            => 'map',
        subdivisions                   => [ 'array', 'map' ],
        time_zone                      => 'utf8_string',
        traits                         => 'map',
        traits                         => 'map',
        type                           => 'utf8_string',
        user_type                      => 'utf8_string',

        # for testing only
        foo       => 'utf8_string',
        bar       => 'utf8_string',
        buzz      => 'utf8_string',
        our_value => 'utf8_string',
    };

    my $callback = sub {
        my $key = shift;

        return $map->{$key} || die <<"ERROR";
Unknown tree key '$key'.

The universal_map_key_type_callback doesn't know what type to use for the passed
key.  If you are adding a new key that will be used in a frozen tree / mmdb then
you should update the mapping in both our internal code and here.
ERROR
    };

    return $callback;
}

sub _write_geoip2_db {
    my $type                            = shift;
    my $populate_all_networks_with_data = shift;
    my $description                     = shift;

    my $writer = MaxMind::DB::Writer::Tree->new(
        ip_version    => 6,
        record_size   => 28,
        ip_version    => 6,
        database_type => $type,
        languages     => [ 'en', $type eq 'GeoIP2-City' ? ('zh') : () ],
        description   => {
            en => ( $type =~ s/-/ /gr )
                . " Test Database (fake GeoIP2 data, for example purposes only)",
            $type eq 'GeoIP2-City' ? ( zh => '小型数据库' ) : (),
        },
        alias_ipv6_to_ipv4    => 1,
        map_key_type_callback => _universal_map_key_type_callback(),
    );

    _populate_all_networks( $writer, $populate_all_networks_with_data )
        if $populate_all_networks_with_data;

    my $value = shift;
    my $nodes
        = decode_json( read_binary("$Dir/$type.json") );

    for my $node (@$nodes) {
        for my $network ( keys %$node ) {
            $writer->insert_network(
                Net::Works::Network->new_from_string( string => $network ),
                $node->{$network}
            );
        }
    }

    open my $output_fh, '>', "$Dir/$type.mmdb";
    $writer->write_tree($output_fh);
    close $output_fh;

    return;
}

sub _populate_all_networks {
    my $writer = shift;
    my $data   = shift;

    my $max_uint128 = uint128(0) - 1;
    my @networks    = Net::Works::Network->range_as_subnets(
        Net::Works::Address->new_from_integer(
            integer => 0,
            version => 6,
        ),
        Net::Works::Address->new_from_integer(
            integer => $max_uint128,
            version => 6,
        ),
    );

    for my $network (@networks) {
        $writer->insert_network( $network => $data );
    }
}

main();