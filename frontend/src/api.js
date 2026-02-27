import axios from 'axios';

let config = {};

export const initApi = (cfg) => {
    config = cfg;
};

export const moodleCall = async (methodname, args) => {
    const url = `${config.wwwroot}/lib/ajax/service.php?sesskey=${config.sesskey}`;
    const payload = [{
        index: 0,
        methodname,
        args
    }];
    const response = await axios.post(url, payload);
    const data = response.data[0];
    if (data.error) {
        throw new Error(data.exception);
    }
    return data.data;
};

export const getCardsByBox = (boxnumber) => {
    return moodleCall('mod_recall_get_cards_by_box', {
        instanceid: config.instanceid,
        boxnumber
    });
};

export const getBoxCounts = async () => {
    // Batch request to get cards for all boxes to count them
    const url = `${config.wwwroot}/lib/ajax/service.php?sesskey=${config.sesskey}`;
    const payload = [0, 1, 2, 3, 4, 5].map(box => ({
        index: box,
        methodname: 'mod_recall_get_cards_by_box',
        args: {
            instanceid: config.instanceid,
            boxnumber: box
        }
    }));
    const response = await axios.post(url, payload);
    const counts = {};
    response.data.forEach((res, i) => {
        if (!res.error) {
            counts[i] = res.data.length;
        } else {
            counts[i] = 0;
        }
    });
    return counts;
};

export const submitAnswer = (cardid, rating) => {
    return moodleCall('mod_recall_submit_answer', {
        cardid,
        rating
    });
};
