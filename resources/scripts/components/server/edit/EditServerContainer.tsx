import TitledGreyBox from '@/components/elements/TitledGreyBox';
import { useStoreState } from '@/state/hooks';
import { ServerContext } from '@/state/server';
import React from 'react';

const EditServerContainer = () => {
    const resources = useStoreState(state => state.user.data!);
    const limits = ServerContext.useStoreState(state => state.server.data!.limits);

    const availableCpu = resources.crCpu - limits.cpu;

    return (
        <TitledGreyBox title={'Edit Server'}>
            Edit your server with this easy-to-use utility.
            <p>
                Available CPU: {availableCpu}
            </p>
        </TitledGreyBox>
    );
};

export default EditServerContainer;
