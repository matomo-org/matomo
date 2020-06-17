const type = require('./type');
const {log, pow, floor, abs} = Math;


const analyze = (data, key=null) => {
    const r = {
        min: Number.MAX_VALUE,
        max: Number.MAX_VALUE*-1,
        sum: 0,
        values: [],
        count: 0
    };
    if (type(data) === 'object') {
        data = Object.values(data);
    }
    data.forEach(val => {
        if (key && type(val) === 'object') val = val[key];
        if (val !== undefined && val !== null && !isNaN(val)) {
            r.values.push(val);
            r.sum += val;
            if (val < r.min) r.min = val;
            if (val > r.max) r.max = val;
            r.count += 1;
        }
    });

    r.domain = [r.min, r.max];

    r.limits = (mode, num) => limits(r, mode, num)

    return r;
};


const limits = (data, mode='equal', num=7) => {
    if (type(data) == 'array') {
        data = analyze(data);
    }
    const {min,max} = data;
    const values = data.values.sort((a,b) => a-b);

    if (num === 1) { return [min,max]; }

    const limits = [];

    if (mode.substr(0,1) === 'c') { // continuous
        limits.push(min);
        limits.push(max);
    }

    if (mode.substr(0,1) === 'e') { // equal interval
        limits.push(min);
        for (let i=1; i<num; i++) {
            limits.push(min+((i/num)*(max-min)));
        }
        limits.push(max);
    }

    else if (mode.substr(0,1) === 'l') { // log scale
        if (min <= 0) {
            throw new Error('Logarithmic scales are only possible for values > 0');
        }
        const min_log = Math.LOG10E * log(min);
        const max_log = Math.LOG10E * log(max);
        limits.push(min);
        for (let i=1; i<num; i++) {
            limits.push(pow(10, min_log + ((i/num) * (max_log - min_log))));
        }
        limits.push(max);
    }

    else if (mode.substr(0,1) === 'q') { // quantile scale
        limits.push(min);
        for (let i=1; i<num; i++) {
            const p = ((values.length-1) * i)/num;
            const pb = floor(p);
            if (pb === p) {
                limits.push(values[pb]);
            } else { // p > pb
                const pr = p - pb;
                limits.push((values[pb]*(1-pr)) + (values[pb+1]*pr));
            }
        }
        limits.push(max);

    }

    else if (mode.substr(0,1) === 'k') { // k-means clustering
        /*
        implementation based on
        http://code.google.com/p/figue/source/browse/trunk/figue.js#336
        simplified for 1-d input values
        */
        let cluster;
        const n = values.length;
        const assignments = new Array(n);
        const clusterSizes = new Array(num);
        let repeat = true;
        let nb_iters = 0;
        let centroids = null;

        // get seed values
        centroids = [];
        centroids.push(min);
        for (let i=1; i<num; i++) {
            centroids.push(min + ((i/num) * (max-min)));
        }
        centroids.push(max);

        while (repeat) {
            // assignment step
            for (let j=0; j<num; j++) {
                clusterSizes[j] = 0;
            }
            for (let i=0; i<n; i++) {
                const value = values[i];
                let mindist = Number.MAX_VALUE;
                let best;
                for (let j=0; j<num; j++) {
                    const dist = abs(centroids[j]-value);
                    if (dist < mindist) {
                        mindist = dist;
                        best = j;
                    }
                    clusterSizes[best]++;
                    assignments[i] = best;
                }
            }

            // update centroids step
            const newCentroids = new Array(num);
            for (let j=0; j<num; j++) {
                newCentroids[j] = null;
            }
            for (let i=0; i<n; i++) {
                cluster = assignments[i];
                if (newCentroids[cluster] === null) {
                    newCentroids[cluster] = values[i];
                } else {
                    newCentroids[cluster] += values[i];
                }
            }
            for (let j=0; j<num; j++) {
                newCentroids[j] *= 1/clusterSizes[j];
            }

            // check convergence
            repeat = false;
            for (let j=0; j<num; j++) {
                if (newCentroids[j] !== centroids[j]) {
                    repeat = true;
                    break;
                }
            }

            centroids = newCentroids;
            nb_iters++;

            if (nb_iters > 200) {
                repeat = false;
            }
        }

        // finished k-means clustering
        // the next part is borrowed from gabrielflor.it
        const kClusters = {};
        for (let j=0; j<num; j++) {
            kClusters[j] = [];
        }
        for (let i=0; i<n; i++) {
            cluster = assignments[i];
            kClusters[cluster].push(values[i]);
        }
        let tmpKMeansBreaks = [];
        for (let j=0; j<num; j++) {
            tmpKMeansBreaks.push(kClusters[j][0]);
            tmpKMeansBreaks.push(kClusters[j][kClusters[j].length-1]);
        }
        tmpKMeansBreaks = tmpKMeansBreaks.sort((a,b)=> a-b);
        limits.push(tmpKMeansBreaks[0]);
        for (let i=1; i < tmpKMeansBreaks.length; i+= 2) {
            const v = tmpKMeansBreaks[i];
            if (!isNaN(v) && (limits.indexOf(v) === -1)) {
                limits.push(v);
            }
        }
    }
    return limits;
}

module.exports = {analyze, limits};
