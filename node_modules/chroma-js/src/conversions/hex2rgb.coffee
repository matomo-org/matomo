
hex2rgb = (hex) ->
    if hex.match /^#?([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/
        if hex.length == 4 or hex.length == 7
            hex = hex.substr(1)
        if hex.length == 3
            hex = hex.split("")
            hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2]
        u = parseInt(hex, 16)
        r = u >> 16
        g = u >> 8 & 0xFF
        b = u & 0xFF
        return [r,g,b,1]

    # match rgba hex format, eg #FF000077
    if hex.match /^#?([A-Fa-f0-9]{8})$/
        if hex.length == 9
            hex = hex.substr(1)
        u = parseInt(hex, 16)
        r = u >> 24 & 0xFF
        g = u >> 16 & 0xFF
        b = u >> 8 & 0xFF
        a = u & 0xFF
        return [r,g,b,a]

    # check for css colors, too
    if rgb = css2rgb hex
        return rgb

    throw "unknown color: "+hex