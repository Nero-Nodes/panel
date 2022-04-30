import Button from '@/components/elements/Button';
import TitledGreyBox from '@/components/elements/TitledGreyBox';
import { ServerContext } from '@/state/server';
import React from 'react';
import tw from 'twin.macro';

const EditServerBox = () => {
    const id = ServerContext.useStoreState(state => state.server.data!.id);

    const editServer = () => {
        // @ts-ignore
        window.location = `/server/${id}/edit`;
    };

    return (
        <TitledGreyBox title={'Edit Server'} css={tw`mb-6 md:mb-10`}>
            Editing your server gives you the ability to add and remove
            resources from your server.
            <div css={tw`mt-6 text-right`}>
                <Button
                    type={'button'}
                    color={'red'}
                    isSecondary
                    onClick={() => editServer()}
                >
                    Edit Server
                </Button>
            </div>
        </TitledGreyBox>
    );
};

export default EditServerBox;
