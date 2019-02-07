###
    chroma.js

    Copyright (c) 2011-2013, Gregor Aisch
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this
      list of conditions and the following disclaimer.

    * Redistributions in binary form must reproduce the above copyright notice,
      this list of conditions and the following disclaimer in the documentation
      and/or other materials provided with the distribution.

    * The name Gregor Aisch may not be used to endorse or promote products
      derived from this software without specific prior written permission.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
    AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
    IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL GREGOR AISCH OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
    INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
    BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
    DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
    OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
    NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
    EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

    @source: https://github.com/gka/chroma.js
###



chroma.analyze = (data, key, filter) ->
    r =
        min: Number.MAX_VALUE
        max: Number.MAX_VALUE*-1
        sum: 0
        values: []
        count: 0

    if not filter?
        filter = ->
            true

    add = (val) ->
        if val? and not isNaN val
            r.values.push val
            r.sum += val
            r.min = val if val < r.min
            r.max = val if val > r.max
            r.count += 1
        return

    visit = (val, k) ->
        if filter val, k
            if key? and type(key) == 'function'
                add key val
            else if key? and type(key) == 'string' or type(key) == 'number'
                add val[key]
            else
                add val

    if type(data) == 'array'
        for val in data
            visit val
    else
        for k, val of data
            visit val, k
    r.domain = [r.min, r.max]
    r.limits = (mode, num) ->
        chroma.limits r, mode, num
    r



chroma.limits = (data, mode='equal', num=7) ->
    if type(data) == 'array'
        data = chroma.analyze data
    min = data.min
    max = data.max
    sum = data.sum
    values = data.values.sort (a,b)->
        a-b

    limits = []

    if mode.substr(0,1) == 'c' # continuous
        limits.push min
        limits.push max

    if mode.substr(0,1) == 'e' # equal interval
        limits.push min
        for i in [1..num-1]
            limits.push min+(i/num)*(max-min)
        limits.push max

    else if mode.substr(0,1) == 'l' # log scale
        if min <= 0
            throw 'Logarithmic scales are only possible for values > 0'
        min_log = Math.LOG10E * Math.log min
        max_log = Math.LOG10E * Math.log max
        limits.push min
        for i in [1..num-1]
            limits.push Math.pow 10, min_log + (i/num) * (max_log - min_log)
        limits.push max

    else if mode.substr(0,1) == 'q' # quantile scale
        limits.push min
        for i in [1..num-1]
            p = values.length * i/num
            pb = Math.floor p
            if pb == p
                limits.push values[pb]
            else # p > pb
                pr = p - pb
                limits.push values[pb]*pr + values[pb+1]*(1-pr)
        limits.push max

    else if mode.substr(0,1) == 'k' # k-means clustering
        ###
        implementation based on
        http://code.google.com/p/figue/source/browse/trunk/figue.js#336
        simplified for 1-d input values
        ###
        n = values.length
        assignments = new Array n
        clusterSizes = new Array num
        repeat = true
        nb_iters = 0
        centroids = null

        # get seed values
        centroids = []
        centroids.push min
        for i in [1..num-1]
            centroids.push min + (i/num) * (max-min)
        centroids.push max

        while repeat
            # assignment step
            for j in [0..num-1]
                clusterSizes[j] = 0
            for i in [0..n-1]
                value = values[i]
                mindist = Number.MAX_VALUE
                for j in [0..num-1]
                    dist = Math.abs centroids[j]-value
                    if dist < mindist
                        mindist = dist
                        best = j
                clusterSizes[best]++
                assignments[i] = best

            # update centroids step
            newCentroids = new Array num
            for j in [0..num-1]
                newCentroids[j] = null
            for i in [0..n-1]
                cluster = assignments[i]
                if newCentroids[cluster] == null
                    newCentroids[cluster] = values[i]
                else
                    newCentroids[cluster] += values[i]
            for j in [0..num-1]
                newCentroids[j] *= 1/clusterSizes[j]

            # check convergence
            repeat = false
            for j in [0..num-1]
                if newCentroids[j] != centroids[i]
                    repeat = true
                    break

            centroids = newCentroids
            nb_iters++

            if nb_iters > 200
                repeat = false

        # finished k-means clustering
        # the next part is borrowed from gabrielflor.it
        kClusters = {}
        for j in [0..num-1]
            kClusters[j] = []
        for i in [0..n-1]
            cluster = assignments[i]
            kClusters[cluster].push values[i]
        tmpKMeansBreaks = []
        for j in [0..num-1]
            tmpKMeansBreaks.push kClusters[j][0]
            tmpKMeansBreaks.push kClusters[j][kClusters[j].length-1]
        tmpKMeansBreaks = tmpKMeansBreaks.sort (a,b)->
            a-b
        limits.push tmpKMeansBreaks[0]
        for i in [1..tmpKMeansBreaks.length-1] by 2
            if not isNaN(tmpKMeansBreaks[i])
                limits.push tmpKMeansBreaks[i]
    limits

